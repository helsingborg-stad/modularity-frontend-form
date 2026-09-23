<?php

declare(strict_types=1);

namespace ModularityFrontendForm\DataProcessor\Handlers\Webhook;

use AcfService\Implementations\FakeAcfService;
use ModularityFrontendForm\Config\Config;
use ModularityFrontendForm\Config\ModuleConfigInterface;
use ModularityFrontendForm\DataProcessor\Handlers\Result\HandlerResultInterface;
use ModularityFrontendForm\DataProcessor\Handlers\WebHookHandler;
use ModularityFrontendForm\DataProcessor\Handlers\HandlerTestCase;
use WP_REST_Request;
use WpService\Implementations\FakeWpService;

require_once dirname(__DIR__, 4) . '/tests/PhpMultipartParser.php';
require_once dirname(__DIR__, 4) . '/tests/HandlerTestCase.php';

final class WebHookV3Test extends HandlerTestCase
{
    private array $requests = [];
    private array $uploads = [];
    private array $temporaryFiles = [];
    private array $extraFields = [];
    private array $headers = [];
    private array $snapshotsDuringSend = [];
    private int $uploadLimit = 8388608;
    private mixed $response = ['response' => ['code' => 201]];
    private array $errors = [];

    protected function tearDown(): void
    {
        parent::tearDown();
        foreach ($this->temporaryFiles as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    /** @dataProvider selections */
    public function testRealPhpParserRetainsImageIdentity(array $selected, array $expected): void
    {
        foreach ($selected as $name) {
            $this->upload('field_' . $name, strtoupper($name) . "\x00\xff\r\nbytes");
        }
        self::assertTrue($this->send('{"acf":{"a":"{{a}}","b":"{{b}}","c":"{{c}}"}}'));
        self::assertCount(1, $this->requests);
        $parsed = PhpMultipartParser::parse($this->requests[0]);
        self::assertSame('3', $parsed['version']);
        self::assertSame(['_acf_rest_payload'], array_keys($parsed['post']));
        self::assertEquals((object) ['acf' => (object) $expected], json_decode($parsed['post']['_acf_rest_payload']));
        self::assertSame($selected === [] ? [] : ['_acf_rest_files'], $parsed['fileFields']);
        self::assertCount(count($selected), $parsed['files']);
        foreach ($expected as $name => $reference) {
            $file = $parsed['files'][substr($reference, 6)];
            self::assertSame('field_' . $name . '.png', $file['name']);
            self::assertSame(UPLOAD_ERR_OK, $file['error']);
            self::assertSame(strtoupper($name) . "\x00\xff\r\nbytes", base64_decode($file['bytes']));
        }
    }

    public static function selections(): array
    {
        return [
            'none' => [[], []],
            'first' => [['a'], ['a' => '$file:file_0']],
            'last' => [['c'], ['c' => '$file:file_0']],
            'B and C' => [['b', 'c'], ['b' => '$file:file_0', 'c' => '$file:file_1']],
            'all' => [['a', 'b', 'c'], ['a' => '$file:file_0', 'b' => '$file:file_1', 'c' => '$file:file_2']],
        ];
    }

    /** @dataProvider transportOutcomes */
    public function testOneAttemptAndSanitizedOutcome(int $status, bool $ok): void
    {
        $secret = 'credential-image-response-secret';
        $this->upload('field_b', $secret);
        $this->headers = [['header' => 'Authorization', 'value' => $secret]];
        $this->response = ['response' => ['code' => $status], 'headers' => ['Retry-After' => '1'],
            'body' => json_encode(['code' => 'acf_rest_upload_in_progress', 'message' => $secret])];
        if ($status === 0) {
            $this->response = $this->createMock(\WP_Error::class);
            $this->response->method('get_error_message')->willReturn($secret);
        } elseif ($status === -1) {
            $this->response = new \RuntimeException($secret);
        }
        self::assertSame($ok, $this->send('{"image":"{{b}}"}'));
        self::assertCount(1, $this->requests);
        self::assertArrayNotHasKey('Idempotency-Key', $this->requests[0]['headers']);
        if (!$ok) {
            $errors = json_encode($this->errors);
            self::assertStringContainsString('remote operation may have succeeded', $errors);
            self::assertStringNotContainsString($secret, $errors);
        }
    }

    public static function transportOutcomes(): array
    {
        return [[200, true], [201, true], [204, true], [299, true], [0, false], [-1, false],
            [100, false], [302, false], [400, false], [409, false], [425, false], [429, false],
            [500, false], [502, false], [503, false], [504, false]];
    }

    public function testRepeatedSourceUsesOneSnapshotAndBinary(): void
    {
        $this->uploadLimit = strlen('shared bytes');
        $this->upload('field_b', 'shared bytes');
        self::assertTrue($this->send('{"acf":{"one":"{{b}}","two":"{{field_b}}"}}'));
        $parsed = PhpMultipartParser::parse($this->requests[0]);
        self::assertSame(['acf' => ['one' => '$file:file_0', 'two' => '$file:file_0']], json_decode($parsed['post']['_acf_rest_payload'], true));
        self::assertCount(1, $parsed['files']);
        self::assertCount(1, $this->snapshotsDuringSend);
        self::assertSame('shared bytes', base64_decode($parsed['files']['file_0']['bytes']));
    }

    /** @dataProvider invalidPlacements */
    public function testInvalidPlacementFailsEvenWhenEmpty(string $body): void
    {
        self::assertFalse($this->send($body));
        $this->upload('field_a', 'selected');
        self::assertFalse($this->send($body));
        self::assertSame([], $this->requests);
    }

    public static function invalidPlacements(): array
    {
        return [['{"x":["{{a}}"]}'], ['{"x":[{"image":"{{a}}"}]}'],
            ['{"x":"text {{a}}"}'], ['{"x":"{{a.0}}"}']];
    }

    /** @dataProvider unsupportedFields */
    public function testUnsupportedSourcesFailWhenEmpty(array $field, string $source): void
    {
        $this->extraFields['field_bad'] = ['key' => 'field_bad', 'name' => 'bad', ...$field];
        self::assertFalse($this->send(json_encode(['x' => '{{' . $source . '}}'])));
        $this->upload('field_bad', 'unsupported upload');
        self::assertFalse($this->send(json_encode(['x' => '{{' . $source . '}}'])));
        self::assertFalse($this->send('{"all":"{{*}}"}'));
        self::assertSame([], $this->requests);
    }

    public static function unsupportedFields(): array
    {
        return [[['type' => 'gallery'], 'bad'], [['type' => 'file'], 'bad'],
            [['type' => 'image', 'parent' => 'field_group'], 'bad'],
            [['type' => 'group', 'sub_fields' => [['type' => 'image', 'name' => 'photo']]], 'bad.photo']];
    }

    public function testMultipleUploadsFailBeforeTransport(): void
    {
        $this->upload('field_a', 'one');
        $this->upload('field_a', 'two');
        self::assertFalse($this->send('{"x":"{{a}}"}'));
        self::assertSame([], $this->requests);
    }

    public function testNestedImageWithNumericParentCannotMasqueradeAsTopLevel(): void
    {
        $this->extraFields['field_group'] = ['key' => 'field_group', 'ID' => 42, 'name' => 'group', 'type' => 'group'];
        $this->extraFields['field_nested'] = ['key' => 'field_nested', 'name' => 'nested', 'type' => 'image', 'parent' => 42];
        $this->upload('field_nested', 'not top level');
        self::assertFalse($this->send('{"image":"{{nested}}"}'));
        self::assertSame([], $this->requests);
    }

    public function testOrdinarySiblingOfNestedImageRetainsHydration(): void
    {
        $this->extraFields['field_group'] = ['key' => 'field_group', 'name' => 'group', 'type' => 'group',
            'sub_fields' => [['key' => 'field_nested', 'name' => 'photo', 'type' => 'image'],
                ['key' => 'field_label', 'name' => 'label', 'type' => 'text']]];
        self::assertTrue($this->send('{"text":"{{group.label}}"}', ['group' => ['label' => 'ordinary']]));
        $parsed = PhpMultipartParser::parse($this->requests[0]);
        self::assertSame(['text' => 'ordinary'], json_decode($parsed['post']['_acf_rest_payload'], true));
    }

    public function testPartialSelectedUploadFailsWithoutTransport(): void
    {
        $before = glob(sys_get_temp_dir() . '/mff-webhook-*');
        $this->upload('field_b', 'complete');
        $this->upload('field_a', 'partial');
        $this->uploads['error']['field_a'][0] = UPLOAD_ERR_PARTIAL;
        self::assertFalse($this->send('{"first":"{{b}}","image":"{{a}}"}'));
        self::assertSame([], $this->requests);
        self::assertSame($before, glob(sys_get_temp_dir() . '/mff-webhook-*'));
        self::assertStringContainsString('not sent', json_encode($this->errors));
    }

    /** @dataProvider cleanupPhases */
    public function testCleanupFailureRetainsOwnershipAndReportsWhetherTransportRan(bool $afterTransport, int $status = 201): void
    {
        $this->upload('field_b', 'private image bytes');
        $this->upload('field_c', 'other handler resource');
        $snapshots = new UploadedFileSnapshots($this->uploads, new FakeAcfService([
            'getFieldObject' => ['key' => 'field_b', 'name' => 'b', 'type' => 'image'],
        ]), '{"image":"{{b}}"}', ['field_b']);
        $path = $snapshots->getFileMap()['file_0']['tmp_name'];
        $blockDeletion = static function () use ($path, $status): array {
            unlink($path);
            mkdir($path); // A directory cannot be unlinked, even when tests run as root.
            return ['response' => ['code' => $status]];
        };
        try {
            if ($afterTransport) {
                $this->response = $blockDeletion;
            } else {
                $blockDeletion();
            }
            self::assertFalse($this->send('{"image":"{{b}}"}', snapshots: $snapshots));
            self::assertCount($afterTransport ? 1 : 0, $this->requests);
            $errors = json_encode($this->errors);
            self::assertStringContainsString('cleanup failed', $errors);
            self::assertStringContainsString($afterTransport ? 'remote operation may have succeeded' : 'not sent', $errors);
            self::assertStringNotContainsString('private image bytes', $errors);
            self::assertStringNotContainsString($path, $errors);
            self::assertCount($afterTransport && $status === 201 ? 1 : 2, $this->errors);
            self::assertDirectoryExists($path);
            self::assertFalse($snapshots->cleanup());
            self::assertCount(1, $snapshots->getFileMap());
        } finally {
            rmdir($path);
            self::assertTrue($snapshots->cleanup());
            self::assertTrue($snapshots->cleanup());
        }
    }

    public static function cleanupPhases(): array
    {
        return [[false], [true], [true, 500]];
    }

    /** @dataProvider byteBudgets */
    public function testActualBytesAndSerializedBudget(int $bytes, int $limit, int $jsonBytes, bool $ok): void
    {
        $this->uploadLimit = $limit;
        if ($bytes > 0) {
            $this->upload('field_a', str_repeat('a', intdiv($bytes, 2)));
            $this->upload('field_b', str_repeat('b', $bytes - intdiv($bytes, 2)));
            $this->uploads['size'] = ['field_a' => [1], 'field_b' => [PHP_INT_MAX]];
        }
        $text = $jsonBytes < 0 ? "\xff" : str_repeat('x', $jsonBytes - 11);
        self::assertSame($ok, $this->send('{"text":"{{text}}","a":"{{a}}","b":"{{b}}"}', ['text' => $text]));
        self::assertCount($ok ? 1 : 0, $this->requests);
        if (!$ok) {
            self::assertStringContainsString('not sent', json_encode($this->errors));
        }
    }

    public static function byteBudgets(): array
    {
        return [
            'image boundary' => [8388608, 67108864, 11, true],
            'image excess' => [8388609, 67108864, 11, false],
            'lower origin boundary' => [4, 4, 11, true],
            'lower origin excess' => [5, 4, 11, false],
            'empty snapshot' => [1, 4, 11, false],
            'JSON boundary' => [0, 8388608, 1048576, true],
            'JSON excess' => [0, 8388608, 1048577, false],
            'serialization failure' => [0, 8388608, -1, false],
        ];
    }

    public function testScalarBrowserUploadAndUnreadableSnapshotChecks(): void
    {
        $this->upload('field_a', 'scalar bytes');
        foreach ($this->uploads as &$values) {
            $values['field_a'] = $values['field_a'][0];
        }
        unset($values);
        self::assertTrue($this->send('{"image":"{{a}}"}'));
        self::assertStringContainsString('scalar bytes', $this->requests[0]['body']);
        $this->requests = [];
        unlink($this->temporaryFiles[0]);
        self::assertFalse($this->send('{"image":"{{a}}"}'));
        self::assertSame([], $this->requests);
    }

    public function testUnselectedBrowserImageOmitsItsProperty(): void
    {
        $this->uploads = ['name' => ['field_a' => ['']], 'type' => ['field_a' => ['']],
            'tmp_name' => ['field_a' => ['']], 'error' => ['field_a' => [UPLOAD_ERR_NO_FILE]], 'size' => ['field_a' => [0]]];
        self::assertTrue($this->send('{"image":"{{a}}","ordinary":123}'));
        self::assertSame([], $this->snapshotsDuringSend);
        $parsed = PhpMultipartParser::parse($this->requests[0]);
        self::assertSame(['ordinary' => 123], json_decode($parsed['post']['_acf_rest_payload'], true));
        self::assertSame([], $parsed['files']);
    }

    public function testJsonKeepsConfiguredHeadersWildcardAndReservedText(): void
    {
        $this->headers = [['header' => 'Content-Type', 'value' => 'application/custom+json']];
        self::assertTrue($this->send('{"all":"{{*}}"}', ['text' => '$file:file_0', 'field_a' => 123], null));
        self::assertSame(['all' => ['text' => '$file:file_0', 'a' => 123]], json_decode($this->requests[0]['body'], true));
        self::assertSame(['Content-Type' => 'application/custom+json'], $this->requests[0]['headers']);
    }

    public function testDefaultAndExplicitJsonKeepLegacyMappingAndHeaders(): void
    {
        foreach ([null, 'json'] as $format) {
            $this->requests = [];
            self::assertTrue($this->send('{"title":"{{title}}","image":"{{image}}","all":"{{*}}"}',
                ['acf' => ['title' => 'Default JSON', 'image' => '999']], $format));
            self::assertCount(1, $this->requests);
            self::assertSame(['Content-Type' => 'application/json'], $this->requests[0]['headers']);
            self::assertArrayNotHasKey('Idempotency-Key', $this->requests[0]['headers']);
            self::assertArrayNotHasKey('X-ACF-Rest-Upload-Version', $this->requests[0]['headers']);
            self::assertSame(['title' => 'Default JSON', 'image' => '999', 'all' => ['title' => 'Default JSON', 'image' => '999']],
                json_decode($this->requests[0]['body'], true));
        }
    }

    public function testJsonRetainsItsExistingTransportError(): void
    {
        $this->response = $this->createMock(\WP_Error::class);
        $this->response->method('get_error_message')->willReturn('legacy transport error');
        self::assertFalse($this->send('{"title":"Example"}', [], 'json'));
        self::assertCount(1, $this->requests);
        self::assertSame(['handler_error' => ['legacy transport error']], $this->errors[0]->errors);
    }

    /** @dataProvider reservedValues */
    public function testReservedReferencesCannotBeForged(string $value, bool $submitted): void
    {
        $this->upload('field_b', 'real bytes');
        $body = json_encode(['image' => '{{b}}', 'text' => $submitted ? '{{text}}' : $value]);
        self::assertFalse($this->send($body, ['text' => $value]));
        self::assertSame([], $this->requests);
    }

    public static function reservedValues(): array
    {
        return [['$file:file_0', false], ['$file:file_0', true], ['$file:unknown', false],
            ['$file:', true], ['$file:bad/key', false], ['$file:bad\nkey', true]];
    }

    public function testWildcardOnlyAndUnreferencedImagesCreateNoSnapshots(): void
    {
        $this->upload('field_a', 'not sent');
        self::assertTrue($this->send('{"all":"{{*}}"}', ['field_a' => 'image metadata', 'title' => 'Example']));
        self::assertSame([], $this->snapshotsDuringSend);
        $parsed = PhpMultipartParser::parse($this->requests[0]);
        self::assertSame([], $parsed['files']);
        self::assertSame(['all' => ['title' => 'Example']], json_decode($parsed['post']['_acf_rest_payload'], true));
    }

    public function testExplicitImageAlongsideWildcardAndOwnedHeaders(): void
    {
        $this->upload('field_b', 'explicit bytes');
        $this->upload('field_c', 'unreferenced bytes');
        $this->headers = [['header' => ' cOnTeNt-TyPe ', 'value' => 'wrong'],
            ['header' => 'X-aCf-ReSt-UpLoAd-VeRsIoN', 'value' => '2'],
            ['header' => 'X-Example', 'value' => 'kept']];
        self::assertTrue($this->send('{"all":"{{*}}","image":"{{b}}"}', ['title' => 'Example']));
        $headers = $this->requests[0]['headers'];
        self::assertCount(3, $headers);
        self::assertSame('kept', $headers['X-Example']);
        self::assertCount(1, $this->snapshotsDuringSend);
        $parsed = PhpMultipartParser::parse($this->requests[0]);
        self::assertSame('3', $parsed['version']);
        self::assertSame(['all' => ['title' => 'Example'], 'image' => '$file:file_0'], json_decode($parsed['post']['_acf_rest_payload'], true));
        self::assertCount(1, $parsed['files']);
    }

    public function testOrdinaryHydrationAndOptionalWrappersRemainCompatible(): void
    {
        $body = '{"null":null,"empty":[],"object":{},"false":"{{false}}","zero":"{{zero}}","id":123,"list":"{{list}}","text":"Hi {{name}}","missing":"{{missing}}","optional":{"$optional":"missing","$value":"ignored"},"present":{"$optional":"zero","$value":"{{zero}}"}}';
        self::assertTrue($this->send($body, ['false' => false, 'zero' => 0, 'list' => [1, 'two'], 'name' => 'reader']));
        $parsed = PhpMultipartParser::parse($this->requests[0]);
        self::assertSame(['null' => null, 'empty' => [], 'object' => [], 'false' => false, 'zero' => 0,
            'id' => 123, 'list' => [1, 'two'], 'text' => 'Hi reader', 'missing' => '', 'optional' => null, 'present' => 0],
            json_decode($parsed['post']['_acf_rest_payload'], true));
    }

    public function testImageInOrdinaryOptionalObjectUsesEffectiveDestination(): void
    {
        $this->upload('field_b', 'wrapped bytes');
        self::assertTrue($this->send('{"acf":{"$optional":"title","$value":{"image":"{{b}}"}}}', ['title' => 'present']));
        $parsed = PhpMultipartParser::parse($this->requests[0]);
        self::assertSame(['acf' => ['image' => '$file:file_0']], json_decode($parsed['post']['_acf_rest_payload'], true));
    }

    public function testAcfMirrorsExposeApprovedTransportAndWildcardHelp(): void
    {
        $directory = dirname(__DIR__, 3) . '/AcfFields';
        $json = json_decode(file_get_contents($directory . '/json/mod-frontend-forms.json'), true);
        $php = file_get_contents($directory . '/php/mod-frontend-forms.php');
        $fields = [];
        $walk = static function (array $items) use (&$walk, &$fields): void {
            foreach ($items as $item) {
                $fields[$item['name']] = $item;
                $walk($item['sub_fields'] ?? []);
            }
        };
        $walk($json[0]['fields']);
        self::assertSame(['json' => 'JSON (no image support)', 'multipart-json' => 'Multipart (image support)'], $fields['requestFormat']['choices']);
        self::assertSame('json', $fields['requestFormat']['default_value']);
        $help = 'In Multipart mode, {{*}} includes ordinary field values but omits image fields. To send an image, map its field explicitly. Use {{image_field}} as the complete value of an object property. Only top-level image fields are supported.';
        self::assertStringContainsString($help, $fields['body']['instructions']);
        self::assertStringContainsString($help, $php);
        self::assertStringContainsString("'json' => __('JSON (no image support)'", $php);
        self::assertStringContainsString("'multipart-json' => __('Multipart (image support)'", $php);
        self::assertStringContainsString("'default_value' => 'json'", $php);
        self::assertStringNotContainsString("'multipart-create' =>", $php);
        self::assertStringNotContainsString("'multipart' =>", $php);
    }

    private function upload(string $field, string $bytes): void
    {
        $path = tempnam(sys_get_temp_dir(), 'v3-fixture-');
        $this->temporaryFiles[] = $path;
        file_put_contents($path, $bytes);
        foreach (['name' => "$field.png", 'type' => 'image/png', 'tmp_name' => $path,
            'error' => UPLOAD_ERR_OK, 'size' => strlen($bytes)] as $attribute => $value) {
            $this->uploads[$attribute][$field][] = $value;
        }
    }

    /** @dataProvider retiredFormats */
    public function testRetiredAndUnknownFormatsDoNotSend(string $format): void
    {
        self::assertFalse($this->send('{"title":"Example"}', [], $format));
        self::assertSame([], $this->requests);
    }

    public static function retiredFormats(): array
    {
        return [['multipart'], ['multipart-create'], ['unknown'], ['']];
    }

    private function send(string $body, array $data = [], ?string $format = 'multipart-json', ?UploadedFileSnapshots $snapshots = null): bool
    {
        $originals = array_filter($this->temporaryFiles, 'is_file');
        $contents = array_map('file_get_contents', $originals);
        $wp = new FakeWpService([
            'wpRemotePost' => function ($url, $args) {
                $this->requests[] = $args;
                $this->snapshotsDuringSend = glob(sys_get_temp_dir() . '/mff-webhook-*') ?: [];
                $response = is_callable($this->response) ? ($this->response)() : $this->response;
                return $response instanceof \Throwable ? throw $response : $response;
            },
            'isWpError' => static fn($response) => $response instanceof \WP_Error,
            'wpRemoteRetrieveResponseCode' => static fn($response) => $response['response']['code'],
            'wpMaxUploadSize' => $this->uploadLimit,
            '__' => static fn($text) => $text,
        ]);
        $fields = [];
        foreach (['a', 'b', 'c'] as $name) {
            $fields['field_' . $name] = ['key' => 'field_' . $name, 'name' => $name, 'type' => 'image', 'parent' => 'group_form'];
        }
        $fields = [...$fields, ...$this->extraFields];
        $acf = new FakeAcfService(['getFieldObject' => static fn($key) => $fields[$key] ?? false]);
        $config = $this->createMock(Config::class);
        $config->method('getFieldNamespace')->willReturn('acf');
        $module = $this->createMock(ModuleConfigInterface::class);
        $settings = (object) ['body' => $body, 'callbackUrl' => 'https://example.test/create'];
        $settings->headers = $this->headers;
        if ($format !== null) {
            $settings->requestFormat = $format;
        }
        $module->method('getWebHookHandlerConfig')->willReturn($settings);
        $module->method('getFieldKeysRegisteredAsFormFields')->willReturn(array_keys($fields));
        $this->errors = [];
        $result = $this->createMock(HandlerResultInterface::class);
        $result->method('setError')->willReturnCallback(function ($error): void { $this->errors[] = $error; });
        $request = $this->createMock(WP_REST_Request::class);
        $request->method('get_file_params')->willReturn(['acf' => $this->uploads]);
        (new WebHookHandler($wp, $acf, $config, $module, $result, uploadedFileSnapshots: $snapshots))->handle($data, $request);
        foreach ($snapshots === null ? $this->snapshotsDuringSend : [] as $path) {
            self::assertFileDoesNotExist($path);
        }
        foreach ($originals as $index => $path) {
            self::assertSame($contents[$index], file_get_contents($path));
        }
        return $this->errors === [];
    }
}

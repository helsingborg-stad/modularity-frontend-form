<?php

declare(strict_types=1);

namespace ModularityFrontendForm\DataProcessor\Handlers\Webhook;

use AcfService\AcfService;
use ModularityFrontendForm\Config\Config;
use ModularityFrontendForm\Config\ModuleConfigInterface;
use ModularityFrontendForm\DataProcessor\Handlers\Result\HandlerResultInterface;
use ModularityFrontendForm\DataProcessor\Handlers\WebHookHandler;
use PHPUnit\Framework\TestCase;
use WP_REST_Request;
use WpService\WpService;

require_once dirname(__DIR__, 4) . '/tests/PhpMultipartParser.php';

final class WebHookV3Test extends TestCase
{
    private array $requests = [];
    private array $uploads = [];
    private array $temporaryFiles = [];
    private array $extraFields = [];
    private array $headers = [];
    private array $snapshotsDuringSend = [];
    private int $uploadLimit = 8388608;

    protected function tearDown(): void
    {
        foreach ($this->temporaryFiles as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    public function testAbsentASelectedBCPreserveIdentity(): void
    {
        $this->upload('field_b', "B\x00\xff\r\nbytes");
        $this->upload('field_c', "C\x00\xfe\r\nbytes");
        self::assertTrue($this->send('{"acf":{"a":"{{a}}","b":"{{b}}","c":"{{c}}"}}'));
        self::assertCount(1, $this->requests);
        $request = $this->requests[0];
        self::assertSame('3', $request['headers']['X-ACF-Rest-Upload-Version'] ?? null);
        self::assertStringContainsString('name="_acf_rest_payload"', $request['body']);
        self::assertStringContainsString('{"acf":{"b":"$file:file_0","c":"$file:file_1"}}', $request['body']);
        self::assertStringContainsString("B\x00\xff\r\nbytes", $request['body']);
        self::assertStringContainsString("C\x00\xfe\r\nbytes", $request['body']);
    }

    /** @dataProvider selections */
    public function testRealPhpParserRetainsImageIdentity(array $selected, array $expected): void
    {
        foreach ($selected as $name) {
            $this->upload('field_' . $name, strtoupper($name) . "\x00\xff\r\nbytes");
        }
        self::assertTrue($this->send('{"acf":{"a":"{{a}}","b":"{{b}}","c":"{{c}}"}}'));
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
        $this->upload('field_a', 'partial');
        $this->uploads['error']['field_a'][0] = UPLOAD_ERR_PARTIAL;
        self::assertFalse($this->send('{"image":"{{a}}"}'));
        self::assertSame([], $this->requests);
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

    private function send(string $body, array $data = [], ?string $format = 'multipart-json'): bool
    {
        $wp = $this->createMock(WpService::class);
        $wp->method('wpRemotePost')->willReturnCallback(function ($url, $args): array {
            $this->requests[] = $args;
            $this->snapshotsDuringSend = glob(sys_get_temp_dir() . '/mff-webhook-*') ?: [];
            return ['response' => ['code' => 201]];
        });
        $wp->method('wpRemoteRetrieveResponseCode')->willReturn(201);
        $wp->method('wpMaxUploadSize')->willReturn($this->uploadLimit);
        $acf = $this->createMock(AcfService::class);
        $fields = [];
        foreach (['a', 'b', 'c'] as $name) {
            $fields['field_' . $name] = ['key' => 'field_' . $name, 'name' => $name, 'type' => 'image', 'parent' => 'group_form'];
        }
        $fields = [...$fields, ...$this->extraFields];
        $acf->method('getFieldObject')->willReturnCallback(static fn($key) => $fields[$key] ?? false);
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
        $ok = true;
        $result = $this->createMock(HandlerResultInterface::class);
        $result->method('setError')->willReturnCallback(static function () use (&$ok): void { $ok = false; });
        $request = $this->createMock(WP_REST_Request::class);
        $request->method('get_file_params')->willReturn(['acf' => $this->uploads]);
        (new WebHookHandler($wp, $acf, $config, $module, (object) [], $result))->handle($data, $request);
        foreach ($this->snapshotsDuringSend as $path) {
            self::assertFileDoesNotExist($path);
        }
        return $ok;
    }
}

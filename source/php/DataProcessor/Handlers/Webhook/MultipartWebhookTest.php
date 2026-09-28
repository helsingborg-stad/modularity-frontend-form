<?php

declare(strict_types=1);

namespace ModularityFrontendForm\DataProcessor\Handlers\Webhook;

use AcfService\Implementations\FakeAcfService;
use ModularityFrontendForm\Config\Config;
use ModularityFrontendForm\Config\ModuleConfigInterface;
use ModularityFrontendForm\DataProcessor\Handlers\HandlerTestCase;
use ModularityFrontendForm\DataProcessor\Handlers\Result\HandlerResultInterface;
use ModularityFrontendForm\DataProcessor\Handlers\WebHookHandler;
use Psr\Log\LoggerInterface;
use WP_REST_Request;
use WpService\Implementations\FakeWpService;

require_once dirname(__DIR__, 4) . '/tests/PhpMultipartParser.php';
require_once dirname(__DIR__, 4) . '/tests/HandlerTestCase.php';

final class MultipartWebhookTest extends HandlerTestCase
{
    private array $uploads = [];
    private array $paths = [];
    private array $requests = [];

    protected function tearDown(): void
    {
        foreach ($this->paths as $path) { if (is_file($path)) { unlink($path); } }
        parent::tearDown();
    }

    public function testMultipartUsesPhpFieldsAndDestinationNamedFiles(): void
    {
        $this->upload('field_image', 'image bytes');
        self::assertTrue($this->send('{"title":"{{title}}","status":"draft","acf":{"image":"{{image}}","location":{"lat":"{{location.lat}}","lng":"{{location.lng}}"}}}',
            ['title' => 'Sponsor', 'location' => ['lat' => 56.1, 'lng' => 12.2]]));
        $parsed = $this->parse($this->requests[0]);
        self::assertSame('true', $parsed['upload']);
        self::assertSame(['title' => 'Sponsor', 'status' => 'draft', 'acf' => ['location' => ['lat' => '56.1', 'lng' => '12.2']]], $parsed['post']);
        self::assertSame(['acf'], $parsed['fileFields']);
        self::assertSame('field_image.png', $parsed['files']['image']['name']);
        self::assertSame('image bytes', base64_decode($parsed['files']['image']['bytes']));
    }

    public function testOptionalSubtreeIsOmittedButExplicitNullIsRejected(): void
    {
        self::assertTrue($this->send('{"acf":{"location":{"$optional":"location","$value":{"lat":"{{location.lat}}","lng":"{{location.lng}}"}}}}'));
        $request = $this->requests[0];
        $this->requests = [];
        self::assertFalse($this->send('{"acf":{"location":null}}'));
        self::assertSame([], $this->requests);
        self::assertSame([], $this->parse($request)['post']);
    }

    /** @dataProvider imageDestinations */
    public function testImageDestinationFollowsTemplate(string $template, string $destination): void
    {
        $this->upload('field_image', "PRIVATE_IMAGE\x00\xff");
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('debug')->with(
            'Prepared multipart webhook structure: {structure}',
            self::callback(static function (array $context) use ($destination): bool {
                $structure = json_decode($context['structure'], true);
                self::assertSame([$destination], $structure['fileFields']);
                self::assertSame(['message'], $structure['fieldNames']);
                foreach (['PRIVATE_IMAGE', 'PRIVATE_MESSAGE', 'field_image.png', 'tmp_name', 'https://'] as $private) {
                    self::assertStringNotContainsString($private, $context['structure']);
                }
                return true;
            })
        );
        self::assertTrue($this->send($template, ['message' => 'PRIVATE_MESSAGE'], 'multipart', $logger));
        self::assertCount(1, $this->requests);
        self::assertStringContainsString('name="' . $destination . '"; filename="field_image.png"', $this->requests[0]['body']);
        self::assertStringContainsString("PRIVATE_IMAGE\x00\xff", $this->requests[0]['body']);
        self::assertSame(1, substr_count($this->requests[0]['body'], 'filename='));
    }

    public static function imageDestinations(): array
    {
        return [
            'root' => ['{"message":"{{message}}","image":"{{image}}"}', 'image'],
            'ACF compatibility' => ['{"message":"{{message}}","acf":{"image":"{{image}}"}}', 'acf[image]'],
            'custom object' => ['{"message":"{{message}}","data":{"photo":"{{image}}"}}', 'data[photo]'],
            'deep object' => ['{"message":"{{message}}","data":{"media":{"photo":"{{image}}"}}}', 'data[media][photo]'],
        ];
    }

    public function testCustomDestinationOmitsMissingImageAndPreservesExistingId(): void
    {
        $template = '{"message":"kept","data":{"photo":"{{image}}"}}';
        self::assertTrue($this->send($template));
        self::assertStringNotContainsString('data[photo]', $this->requests[0]['body']);
        self::assertTrue($this->send($template, ['image' => 494]));
        self::assertStringContainsString("name=\"data[photo]\"\r\n\r\n494\r\n", $this->requests[1]['body']);
        $this->upload('field_image', 'image bytes');
        self::assertFalse($this->send($template, ['image' => 494]));
        self::assertCount(2, $this->requests);
    }

    /** @dataProvider invalidImageMappings */
    public function testInvalidImageMappingStillDoesNotSend(string $template): void
    {
        $this->upload('field_image', 'image bytes');
        self::assertFalse($this->send($template));
        self::assertSame([], $this->requests);
    }

    public static function invalidImageMappings(): array
    {
        return [
            ['{"image":"{{image.0}}"}'],
            ['{"image":"prefix {{image}}"}'],
            ['{"data":[{"image":"{{image}}"}]}'],
            ['{"data[photo]":"{{image}}"}'],
            ['{"":"{{image}}"}'],
        ];
    }

    public function testExistingImageIdIsAnOrdinaryFieldAndConflictsWithUpload(): void
    {
        self::assertTrue($this->send('{"acf":{"image":"{{image}}"}}', ['image' => 494]));
        $request = $this->requests[0];
        $this->requests = [];
        $this->upload('field_image', 'image bytes');
        self::assertFalse($this->send('{"acf":{"image":"{{image}}"}}', ['image' => 494]));
        self::assertSame([], $this->requests);
        self::assertSame(['acf' => ['image' => '494']], $this->parse($request)['post']);
    }

    public function testDefaultJsonIsUnchangedAndUnsupportedFormatsDoNotSend(): void
    {
        self::assertTrue($this->send('{"title":"{{title}}"}', ['title' => 'JSON'], 'json'));
        self::assertSame(['Content-Type' => 'application/json'], $this->requests[0]['headers']);
        self::assertSame('{"title":"JSON"}', $this->requests[0]['body']);
        $this->requests = [];
        self::assertFalse($this->send('{"title":"JSON"}', [], 'multipart-json'));
        self::assertSame([], $this->requests);
    }

    public function testMultipartWildcardOmitsImagesAndFieldsHaveABudget(): void
    {
        self::assertTrue($this->send('{"all":"{{*}}"}', ['title' => 'Example', 'image' => 494]));
        self::assertStringContainsString('name="all[title]"', $this->requests[0]['body']);
        self::assertStringNotContainsString('all[image]', $this->requests[0]['body']);
        $this->requests = [];
        self::assertFalse($this->send('{"title":"{{title}}"}', ['title' => str_repeat('a', 1_048_577)]));
        self::assertSame([], $this->requests);
    }

    private function upload(string $field, string $bytes): void
    {
        $path = tempnam(sys_get_temp_dir(), 'multipart-fixture-');
        $this->paths[] = $path;
        file_put_contents($path, $bytes);
        foreach (['name' => "$field.png", 'type' => 'image/png', 'tmp_name' => $path,
            'error' => UPLOAD_ERR_OK, 'size' => strlen($bytes)] as $attribute => $value) {
            $this->uploads[$attribute][$field][] = $value;
        }
    }

    private function parse(array $request): array
    {
        try {
            return PhpMultipartParser::parse($request);
        } catch (\RuntimeException $exception) {
            if ($exception->getMessage() === 'PHP parser did not become ready.') {
                self::markTestSkipped('The sandbox cannot start the loopback PHP parser.');
            }
            throw $exception;
        }
    }

    private function send(string $body, array $data = [], string $format = 'multipart', ?LoggerInterface $logger = null): bool
    {
        $wp = new FakeWpService(['wpRemotePost' => function ($url, $args) { $this->requests[] = $args; return ['response' => ['code' => 201]]; },
            'isWpError' => false, 'wpRemoteRetrieveResponseCode' => 201, 'wpMaxUploadSize' => 8388608, '__' => static fn ($text) => $text]);
        $fields = ['field_image' => ['key' => 'field_image', 'name' => 'image', 'type' => 'image', 'parent' => 'group_form']];
        $acf = new FakeAcfService(['getFieldObject' => static fn ($key) => $fields[$key] ?? false]);
        $config = $this->createMock(Config::class);
        $config->method('getFieldNamespace')->willReturn('acf');
        $module = $this->createMock(ModuleConfigInterface::class);
        $module->method('getWebHookHandlerConfig')->willReturn((object) ['body' => $body, 'callbackUrl' => 'https://example.test/create', 'requestFormat' => $format]);
        $module->method('getFieldKeysRegisteredAsFormFields')->willReturn(array_keys($fields));
        $errors = [];
        $result = $this->createMock(HandlerResultInterface::class);
        $result->method('setError')->willReturnCallback(static function ($error) use (&$errors): void { $errors[] = $error; });
        $request = $this->createMock(WP_REST_Request::class);
        $request->method('get_file_params')->willReturn(['acf' => $this->uploads]);
        (new WebHookHandler($wp, $acf, $config, $module, $result, $logger ?? new \Psr\Log\NullLogger()))->handle(['acf' => $data], $request);
        return $errors === [];
    }
}

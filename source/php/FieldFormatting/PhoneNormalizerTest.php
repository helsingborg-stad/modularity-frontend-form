<?php

namespace ModularityFrontendForm\FieldFormatting;

use AcfService\AcfService;
use ModularityFrontendForm\Config\ConfigInterface;
use ModularityFrontendForm\Config\ModuleConfigInterface;
use ModularityFrontendForm\DataProcessor\DataProcessor;
use ModularityFrontendForm\DataProcessor\FileHandlers\FileHandlerInterface;
use ModularityFrontendForm\DataProcessor\Handlers\HandlerInterface;
use ModularityFrontendForm\DataProcessor\Handlers\HandlerTestCase;
use ModularityFrontendForm\DataProcessor\Handlers\MailHandler;
use ModularityFrontendForm\DataProcessor\Handlers\WebHookHandler;
use ModularityFrontendForm\DataProcessor\Handlers\WpDbHandler;
use ModularityFrontendForm\DataProcessor\Validators\ValidatorInterface;
use ModularityFrontendForm\DataProcessor\Validators\Result\ValidationResult;
use ModularityFrontendForm\FieldValidation\PhoneValidation;
use WP_Error;
use WP_REST_Request;
use WpService\WpService;

require_once dirname(__DIR__, 2) . '/tests/HandlerTestCase.php';

function acf_get_field($key) {
    $field = ['key' => $key, 'name' => substr($key, 6), 'label' => $key, 'type' => $key === 'field_rows' ? 'repeater' : 'text'];
    if ($key !== 'field_missing') {
        $field['phone_validation'] = $key === 'field_none' ? 'none' : 'valid';
    }
    return $field;
}

class PhoneNormalizerTest extends HandlerTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \Brain\Monkey\setUp();
        \Brain\Monkey\Functions\when('acf_get_field')->alias(fn($key) => acf_get_field($key));
    }

    protected function tearDown(): void
    {
        \Brain\Monkey\tearDown();
        parent::tearDown();
    }

    /** @dataProvider storageModes */
    public function testActualHandlersReceiveSameNormalizedValues(bool $useStorage): void
    {
        \Patchwork\redefine('esc_html', fn($value) => htmlspecialchars((string) $value, ENT_QUOTES));
        $wp = $this->createMock(WpService::class);
        $wp->method('__')->willReturnArgument(0);
        $wp->method('sanitizeTextField')->willReturnCallback(fn($value) => (string) $value);
        $wp->method('isWpError')->willReturn(false);
        $wp->method('wpInsertPost')->willReturn(123);
        $wp->method('wpRemoteRetrieveResponseCode')->willReturn(200);
        $config = $this->createMock(ConfigInterface::class);
        $config->method('getFieldNamespace')->willReturn('form');
        $config->method('getMetaDataNamespace')->willReturnCallback(fn($key) => 'meta_' . $key);
        $config->method('getUnprintableKeys')->willReturn([]);
        $config->method('getAllowedHtmlTags')->willReturn([]);
        $module = $this->createMock(ModuleConfigInterface::class);
        $module->method('getModuleId')->willReturn(7);
        $module->method('getModuleTitle')->willReturn('Test form');
        $module->method('getWpDbHandlerConfig')->willReturn((object) ['saveToPostType' => 'post', 'saveToPostTypeStatus' => 'draft']);
        $module->method('getMailHandlerConfig')->willReturn((object) ['Recivers' => [['Email' => 'local@example.invalid']]]);
        $module->method('getWebHookHandlerConfig')->willReturn((object) ['callbackUrl' => 'https://example.invalid', 'body' => '{"data":"{{ * }}"}']);
        $acf = $this->createMock(AcfService::class);
        $acf->method('getFieldObject')->willReturnCallback(fn($key) => acf_get_field($key));
        $files = $this->createMock(FileHandlerInterface::class);
        $files->method('handle')->willReturn([]);
        $input = ['form' => [
            'field_phone' => '070-123 45 67', 'field_international' => '0046 70 123 45 67',
            'field_none' => '070.123.45.67', 'field_missing' => 'ordinary text', 'field_empty' => '',
            'field_rows' => [0 => ['field_phone' => '0047 406 12 345'], 2 => ['field_phone' => '+46 70 123 45 67']],
        ]];
        $expected = $input['form'];
        $expected['field_phone'] = $expected['field_international'] = '+46701234567';
        $expected['field_rows'][0]['field_phone'] = '+4740612345';
        $expected['field_rows'][2]['field_phone'] = '+46701234567';
        $stored = [];
        $acf->expects($useStorage ? $this->exactly(count($expected)) : $this->never())->method('updateField')->willReturnCallback(function ($key, $value) use (&$stored) {
            $stored[$key] = $value;
            return true;
        });
        $wp->expects($this->once())->method('wpMail')->willReturnCallback(function ($to, $subject, $body) {
            foreach (['+46701234567', '+4740612345', '070.123.45.67', 'ordinary text'] as $value) {
                $this->assertStringContainsString($value, $body);
            }
            $this->assertStringNotContainsString('070-123 45 67', $body);
            $this->assertStringNotContainsString('0046', $body);
            return true;
        });
        $wp->expects($this->once())->method('wpRemotePost')->willReturnCallback(function ($url, $args) {
            $data = json_decode($args['body'], true)['data'];
            $this->assertSame('+46701234567', $data['phone']);
            $this->assertSame('+46701234567', $data['international']);
            $this->assertSame('+4740612345', $data['rows'][0]['phone']);
            $this->assertSame('+46701234567', $data['rows'][2]['phone']);
            $this->assertSame('070.123.45.67', $data['none']);
            $this->assertSame('ordinary text', $data['missing']);
            $this->assertSame('', $data['empty']);
            return ['response' => ['code' => 200]];
        });
        $handlers = [new MailHandler($wp, $acf, $config, $module, (object) [], fileHandler: $files), new WebHookHandler($wp, $acf, $config, $module)];
        if ($useStorage) array_unshift($handlers, new WpDbHandler($wp, $acf, $config, $module, (object) [], fileHandler: $files));
        $validator = $this->createMock(ValidatorInterface::class);
        $validator->method('validate')->willReturn(new ValidationResult());
        $processor = new DataProcessor([$validator], $handlers, null, new PhoneNormalizer(new PhoneValidation($wp), $config));
        $this->assertTrue($processor->process($input, $this->createMock(WP_REST_Request::class)));
        if ($useStorage) $this->assertSame($expected, $stored);
        $this->assertSame('070-123 45 67', $input['form']['field_phone']);
    }

    public static function storageModes(): array { return [[true], [false]]; }

    public function testFailedValidationDoesNotNormalizeOrCallHandlers(): void
    {
        $result = new ValidationResult();
        $result->setError(new WP_Error('validation_error', 'Earlier error'));
        $validator = $this->createMock(ValidatorInterface::class);
        $validator->method('validate')->willReturn($result);
        $normalizer = $this->createMock(PhoneNormalizer::class);
        $normalizer->expects($this->never())->method('normalize');
        $handler = $this->createMock(HandlerInterface::class);
        $handler->expects($this->never())->method('handle');
        $processor = new DataProcessor([$validator], [$handler], null, $normalizer);
        $this->assertFalse($processor->process(['form' => ['field_phone' => '070ABC1234']], $this->createMock(WP_REST_Request::class)));
        $this->assertSame('Earlier error', $processor->getFirstError()->errors['validation_error'][0]);
    }
}

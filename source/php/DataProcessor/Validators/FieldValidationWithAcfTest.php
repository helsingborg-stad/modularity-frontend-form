<?php

namespace ModularityFrontendForm\DataProcessor\Validators;

use AcfService\AcfService;
use ModularityFrontendForm\Config\ConfigInterface;
use ModularityFrontendForm\Config\ModuleConfigInterface;
use ModularityFrontendForm\DataProcessor\DataProcessor;
use ModularityFrontendForm\DataProcessor\Handlers\HandlerTestCase;
use WP_REST_Request;
use WpService\WpService;

require_once dirname(__DIR__, 3) . '/tests/HandlerTestCase.php';

function acf_get_field($key) {
    return ['key' => $key, 'label' => 'Phone', 'type' => $key === 'field_rows' ? 'repeater' : 'text',
        'sub_fields' => [['key' => 'field_phone', 'type' => 'text', 'label' => 'Phone']]];
}
function acf_get_validation_errors() { return FieldValidationWithAcfTest::$errors; }
function acf_validate_value($value, $field, $input) {
    FieldValidationWithAcfTest::$calls[] = [$value, $input];
    if (is_array($value)) {
        foreach ($value as $row => $fields) {
            foreach ($fields as $key => $child) {
                acf_validate_value($child, ['key' => $key], "{$input}[{$row}][{$key}]");
            }
        }
        return true; // ACF repeater reports child errors without failing its own result.
    }
    if ($value === 'bad') {
        FieldValidationWithAcfTest::$errors[] = ['input' => $input, 'message' => 'Use + or 00'];
        return false;
    }
    return true;
}

class FieldValidationWithAcfTest extends HandlerTestCase
{
    public static array $errors = [];
    public static array $calls = [];

    private function validator(): FieldValidationWithAcf
    {
        $wp = $this->createMock(WpService::class);
        $wp->method('__')->willReturnArgument(0);
        $config = $this->createMock(ConfigInterface::class);
        $config->method('getFieldNamespace')->willReturnCallback(fn($key = null) => 'form' . ($key ? "[$key]" : ''));
        $config->method('getKeysToBypass')->willReturn(['form', 'nonce']);
        return new FieldValidationWithAcf($wp, $this->createMock(AcfService::class), $config, $this->createMock(ModuleConfigInterface::class));
    }

    public function testNamespacedRepeaterPreservesCurrentMessageAndRowWithoutClearingEarlierErrors(): void
    {
        self::$errors = [['input' => 'unrelated', 'message' => 'Earlier']];
        self::$calls = [];
        $request = $this->createMock(WP_REST_Request::class);
        $processor = new DataProcessor([$this->validator()], [], null);
        $data = ['nonce' => 'ignored', 'form' => ['field_rows' => [
            ['field_phone' => '0047 406 12 345'], ['field_phone' => 'bad'], ['field_phone' => 'bad'],
        ]]];
        self::assertFalse($processor->process($data, $request));
        $first = $processor->getFirstError();
        self::assertSame(['Use + or 00'], $first->errors['validation_error']);
        self::assertSame('form[field_rows][1][field_phone]', $first->error_data['validation_error']['fields']['input']);
        self::assertSame(400, $first->error_data['validation_error']['status']);
        self::assertSame($first, $processor->getFirstError());
        self::assertCount(2, $processor->getErrors());
        self::assertSame('Earlier', self::$errors[0]['message']);
        self::assertSame('0047 406 12 345', self::$calls[1][0]);
    }

    public function testCorrectedOptionalAndOmittedConditionalFieldsPass(): void
    {
        self::$errors = [];
        self::$calls = [];
        $result = $this->validator()->validate(['form' => ['field_phone' => '070-123 45 67', 'field_optional' => '']], $this->createMock(WP_REST_Request::class));
        self::assertTrue($result->getIsValid());
        self::assertCount(2, self::$calls);
    }

    /** @dataProvider malformedFields */
    public function testMalformedTextAndRowsFailBeforeAcf(mixed $fields, string $input): void
    {
        self::$calls = [];
        $result = $this->validator()->validate(['form' => $fields], $this->createMock(WP_REST_Request::class));
        self::assertFalse($result->getIsValid());
        self::assertSame([], self::$calls);
        self::assertSame($input, $result->getErrors()[0]->error_data['validation_error']['fields']['input']);
    }

    public static function malformedFields(): array
    {
        return [
            [['field_phone' => []], 'form[field_phone]'],
            [['field_phone' => 123], 'form[field_phone]'],
            [['field_rows' => ['bad']], 'form[field_rows]'],
            [['field_rows' => [['field_phone' => []]]], 'form[field_rows][0][field_phone]'],
        ];
    }

    public function testMalformedNamespaceFailsWithoutCallingAcf(): void
    {
        self::$calls = [];
        self::assertFalse($this->validator()->validate(['form' => 'bad'], $this->createMock(WP_REST_Request::class))->getIsValid());
        self::assertSame([], self::$calls);
    }
}

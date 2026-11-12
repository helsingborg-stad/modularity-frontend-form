<?php

namespace ModularityFrontendForm\DataProcessor\Validators;

use AcfService\AcfService;
use ModularityFrontendForm\Config\ConfigInterface;
use ModularityFrontendForm\Config\ModuleConfigInterface;
use ModularityFrontendForm\DataProcessor\Handlers\HandlerTestCase;
use WP_REST_Request;
use WpService\WpService;

require_once dirname(__DIR__, 3) . '/tests/HandlerTestCase.php';

class FieldsExistsOnPostTypeTest extends HandlerTestCase
{
    /** @dataProvider submittedFields */
    public function testValidatesFormKeysRatherThanUpdateTransportParameters(array $fields, bool $valid): void
    {
        $wp = $this->createMock(WpService::class);
        $wp->method('__')->willReturnArgument(0);
        $acf = $this->createMock(AcfService::class);
        $acf->method('getFieldGroups')->with(['post_type' => 'submission'])->willReturn([['key' => 'group_form']]);
        $acf->method('acfGetFields')->with('group_form')->willReturn([['key' => 'field_registered']]);
        $config = $this->createMock(ConfigInterface::class);
        $config->method('getFieldNamespace')->willReturn('form');
        $config->method('getKeysToBypass')->willReturn(['form', 'module-id', 'nonce', 'post_title', 'post_content']);
        $module = $this->createMock(ModuleConfigInterface::class);
        $module->method('getWpDbHandlerConfig')->willReturn((object) ['saveToPostType' => 'submission']);
        $validator = new FieldsExistsOnPostType($wp, $acf, $config, $module);
        $result = $validator->validate([
            'module-id' => 20, 'post-id' => 123, 'token' => 'test-token', 'nonce' => 'test-nonce', 'form' => $fields,
        ], $this->createMock(WP_REST_Request::class));
        self::assertSame($valid, $result->getIsValid());
        if (!$valid) {
            self::assertSame(['field_unmapped'], array_values($result->getErrors()[0]->get_error_data('validation_error')['fields']));
        }
    }

    public static function submittedFields(): array
    {
        return [
            [['field_registered' => 'value', 'post_title' => 'Title'], true],
            [['field_registered' => 'value', 'field_unmapped' => 'value'], false],
        ];
    }
}

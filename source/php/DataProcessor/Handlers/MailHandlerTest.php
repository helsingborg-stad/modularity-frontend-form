<?php

namespace ModularityFrontendForm\DataProcessor\Handlers;

use AcfService\AcfService;
use ModularityFrontendForm\Config\ConfigInterface;
use ModularityFrontendForm\Config\ModuleConfigInterface;
use WP_REST_Request;
use WpService\WpService;

require_once dirname(__DIR__, 3) . '/tests/HandlerTestCase.php';

function acf_get_field($key) { return ['label' => $key]; }

class MailHandlerTest extends HandlerTestCase
{
    public function testMailReadsNamespacedFieldsAndRetainsRepeaterValues(): void
    {
        \Patchwork\redefine('esc_html', fn($value) => htmlspecialchars((string) $value, ENT_QUOTES));
        $wp = $this->createMock(WpService::class);
        $wp->method('__')->willReturnArgument(0);
        $wp->method('sanitizeTextField')->willReturnCallback(fn($value) => strip_tags((string) $value));
        $wp->expects($this->once())->method('wpMail')->willReturnCallback(function ($to, $subject, $body) {
            $this->assertSame(['local@example.invalid'], $to);
            $this->assertStringContainsString('+46701234567', $body);
            $this->assertStringContainsString('+4740612345', $body);
            $this->assertStringContainsString('Plain text', $body);
            $this->assertStringNotContainsString('ignored nonce', $body);
            $this->assertStringNotContainsString('<b>', $body);
            return true;
        });
        $config = $this->createMock(ConfigInterface::class);
        $config->method('getFieldNamespace')->willReturn('form');
        $config->method('getUnprintableKeys')->willReturn(['nonce']);
        $module = $this->createMock(ModuleConfigInterface::class);
        $module->method('getMailHandlerConfig')->willReturn((object) ['Recivers' => [['Email' => 'local@example.invalid']]]);
        $module->method('getModuleTitle')->willReturn('Test form');
        $handler = new MailHandler($wp, $this->createMock(AcfService::class), $config, $module, (object) [],
            fileHandler: $this->createMock(\ModularityFrontendForm\DataProcessor\FileHandlers\FileHandlerInterface::class));
        $result = $handler->handle(['nonce' => 'ignored nonce', 'form' => [
            'field_phone' => '+46701234567', 'field_rows' => [['field_phone' => '+4740612345']],
            'field_plain' => '<b>Plain text</b>',
        ]], $this->createMock(WP_REST_Request::class));
        $this->assertTrue($result->isOk());
    }
}

<?php

namespace ModularityFrontendForm\DataProcessor\Handlers\Webhook;

use AcfService\Implementations\FakeAcfService;
use ModularityFrontendForm\Config\Config;
use ModularityFrontendForm\Config\ModuleConfigInterface;
use ModularityFrontendForm\DataProcessor\Handlers\WebHookHandler;
use PHPUnit\Framework\TestCase;
use WpService\Implementations\FakeWpService;

class PhoneNumberWebhookTest extends TestCase
{
    public function testSendsFormattedPhonesIncludingDistinctRepeaterValues(): void
    {
        $fields = [
            'field_phone' => ['name' => 'phone', 'type' => 'phone_number', 'default_region' => 'SE'],
            'field_text' => ['name' => 'text', 'type' => 'text'],
            'field_contacts' => ['name' => 'contacts', 'type' => 'repeater'],
            'field_row_phone' => ['name' => 'row_phone', 'type' => 'phone_number', 'default_region' => 'DK'],
        ];
        $requests = [];
        $wp = new FakeWpService([
            'applyFilters' => static function ($hook, $value, ...$args) {
                if ($hook !== 'acf/format_value') return $value;
                // Stub the formatter; this test only checks the outgoing payload.
                $field = $args[1];
                self::assertSame('phone_number', $field['type']);
                return [
                    'SE' => ['0700000000' => '+46700000000'],
                    'DK' => ['20123456' => '+4520123456', '20123457' => '+4520123457'],
                ][$field['default_region']][$value];
            },
            'wpRemotePost' => static function ($url, $args) use (&$requests) { $requests[] = $args; return []; },
            'wpRemoteRetrieveResponseCode' => 200, 'isWpError' => false,
        ]);
        $acf = new FakeAcfService(['getFieldObject' => static fn ($key) => $fields[$key] ?? false]);
        $module = $this->createMock(ModuleConfigInterface::class);
        $module->method('getWebHookHandlerConfig')->willReturn((object) [
            'callbackUrl' => 'https://example.test/submit',
            'body' => '{"phone":"{{phone}}","text":"{{text}}","contacts":"{{contacts}}"}',
        ]);
        $handler = new WebHookHandler($wp, $acf, new Config($wp, 'test'), $module);
        $result = $handler->handle(['mod-frontend-form' => [
            'field_phone' => '0700000000', 'field_text' => '0700000000',
            'field_contacts' => [['field_row_phone' => '20123456'], ['field_row_phone' => '20123457']],
        ]], $this->createMock(\WP_REST_Request::class));

        self::assertTrue($result->isOk());
        self::assertCount(1, $requests);
        self::assertSame(['phone' => '+46700000000', 'text' => '0700000000',
            'contacts' => [['row_phone' => '+4520123456'], ['row_phone' => '+4520123457']]],
            json_decode($requests[0]['body'], true));
    }
}

<?php

namespace ModularityFrontendForm\Api\Submit;

use AcfService\AcfService;
use ModularityFrontendForm\Config\ConfigInterface;
use ModularityFrontendForm\Config\ModuleConfigFactory;
use ModularityFrontendForm\DataProcessor\Handlers\HandlerFactory;
use ModularityFrontendForm\DataProcessor\Handlers\HandlerInterface;
use ModularityFrontendForm\DataProcessor\Handlers\Result\HandlerResult;
use ModularityFrontendForm\DataProcessor\Validators\ValidatorFactory;
use ModularityFrontendForm\DataProcessor\Validators\ValidatorInterface;
use ModularityFrontendForm\DataProcessor\Validators\Result\ValidationResult;
use PHPUnit\Framework\TestCase;
use PsrLogger\NullLoggerFactory;
use WP_REST_Request;
use WP_REST_Response;
use WpService\WpService;

class PhoneNormalizationTest extends TestCase
{
    protected function setUp(): void
    {
        \Brain\Monkey\setUp();
        \Brain\Monkey\Functions\when('acf_get_field')->justReturn(['type' => 'text', 'phone_validation' => 'valid']);
    }

    protected function tearDown(): void { \Brain\Monkey\tearDown(); }

    /** @dataProvider routes */
    public function testBothRoutesValidateEnvelopeBeforeNormalizingHandlerInput(string $route): void
    {
        $input = ['module-id' => 20, 'form' => ['field_phone' => '070-123 45 67']];
        $request = $this->createMock(WP_REST_Request::class);
        $request->method('get_params')->willReturn($input);
        $wp = $this->createMock(WpService::class);
        $wp->method('restEnsureResponse')->willReturn($this->createMock(WP_REST_Response::class));
        $config = $this->createMock(ConfigInterface::class);
        $config->method('getFieldNamespace')->willReturn('form');
        $validator = $this->createMock(ValidatorInterface::class);
        $validator->expects($this->once())->method('validate')->with($input, $request)->willReturn(new ValidationResult());
        $validators = $this->createMock(ValidatorFactory::class);
        $validators->method('createInsertValidators')->willReturn([$validator]);
        $handler = $this->createMock(HandlerInterface::class);
        $handler->expects($this->once())->method('handle')->with(
            ['module-id' => 20, 'form' => ['field_phone' => '+46701234567']], $request
        )->willReturn(new HandlerResult());
        $handlers = $this->createMock(HandlerFactory::class);
        $handlers->method('createHandlers')->willReturn([$handler]);
        $handlers->method('createNullHandler')->willReturn($this->createMock(HandlerInterface::class));
        $endpoint = new $route($wp, $this->createMock(AcfService::class), $config,
            $this->createMock(ModuleConfigFactory::class), $validators, $handlers, new NullLoggerFactory());
        $endpoint->handleRequest($request);
    }

    public static function routes(): array { return [[Post::class], [Update::class]]; }
}

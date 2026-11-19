<?php

namespace ModularityFrontendForm\Api\Submit;

use AcfService\Implementations\FakeAcfService;
use Brain\Monkey;
use Brain\Monkey\Functions;
use ModularityFrontendForm\Config\Config;
use ModularityFrontendForm\Config\ModuleConfigFactory;
use ModularityFrontendForm\Config\ModuleConfigInterface;
use ModularityFrontendForm\DataProcessor\Handlers\HandlerFactory;
use ModularityFrontendForm\DataProcessor\Handlers\HandlerInterface;
use ModularityFrontendForm\DataProcessor\Validators\FieldValidationWithAcf;
use ModularityFrontendForm\DataProcessor\Validators\ValidatorFactory;
use PHPUnit\Framework\TestCase;
use PsrLogger\NullLoggerFactory;
use WpService\Implementations\FakeWpService;

class AcfValidationTest extends TestCase
{
    protected function setUp(): void
    {
        Monkey\setUp();
        // Installed WordPress stubs have no behavior; expose only the error boundary used here.
        \Patchwork\redefine('WP_Error::__construct', function ($code = '', $message = '', $data = '') {
            $this->errors = [$code => [$message]];
            $this->error_data = [$code => $data];
        });
        \Patchwork\redefine('WP_Error::get_error_code', function () { return array_key_first($this->errors); });
        \Patchwork\redefine('WP_Error::get_error_data', function ($code = '') {
            return $this->error_data[$code ?: $this->get_error_code()] ?? null;
        });
    }

    protected function tearDown(): void
    {
        Monkey\tearDown();
    }

    public function endpoints(): array
    {
        return ['create' => [Post::class], 'update' => [Update::class]];
    }

    /** @dataProvider endpoints */
    public function testReturnsOriginalMessagesPerFieldWithoutInvokingHandlers(string $endpoint): void
    {
        $values = ['field_email' => 'invalid', 'field_name' => ''];
        $messages = ['field_email' => 'Enter a valid email address', 'field_name' => 'Name is required'];
        $errors = [];
        Functions\when('acf_reset_validation_errors')->alias(function () use (&$errors) { $errors = []; });
        Functions\when('acf_get_validation_errors')->alias(function () use (&$errors) { return $errors; });
        Functions\when('acf_get_field')->alias(fn ($key) => ['key' => $key]);
        Functions\when('acf_validate_value')->alias(function ($value, $field, $input) use (&$errors, $values, $messages) {
            self::assertSame($values[$field['key']], $value);
            $errors[] = ['input' => $input, 'message' => $messages[$field['key']]];
            return false;
        });
        $wp = new FakeWpService(['applyFilters' => fn ($hook, $value) => $value,
            '__' => fn ($message) => $message, 'restEnsureResponse' => fn ($response) => $response]);
        $validator = new FieldValidationWithAcf($wp, new FakeAcfService(), new Config($wp, 'test'),
            $this->createMock(ModuleConfigInterface::class));
        $validators = $this->createMock(ValidatorFactory::class);
        $validators->method('createInsertValidators')->willReturn([$validator]);
        $handler = $this->createMock(HandlerInterface::class);
        $handler->expects($this->never())->method('handle');
        $handlers = $this->createMock(HandlerFactory::class);
        $handlers->method('createHandlers')->willReturn([$handler]);
        $request = $this->createMock(\WP_REST_Request::class);
        $request->method('get_params')->willReturn(['module-id' => 12, 'post-id' => 34, 'mod-frontend-form' => $values]);
        $sut = new $endpoint($wp, new FakeAcfService(), new Config($wp, 'test'),
            $this->createMock(ModuleConfigFactory::class), $validators, $handlers, new NullLoggerFactory());

        $response = $sut->handleRequest($request);

        self::assertInstanceOf(\WP_Error::class, $response);
        self::assertSame(['status' => 400, 'field_errors' => [
            ['input' => 'mod-frontend-form[field_email]', 'message' => $messages['field_email']],
            ['input' => 'mod-frontend-form[field_name]', 'message' => $messages['field_name']],
        ]], $response->get_error_data());
    }
}

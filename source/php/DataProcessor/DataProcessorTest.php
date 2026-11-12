<?php

namespace ModularityFrontendForm\DataProcessor;

use ModularityFrontendForm\DataProcessor\Handlers\HandlerInterface;
use ModularityFrontendForm\DataProcessor\Handlers\Result\HandlerResult;
use ModularityFrontendForm\DataProcessor\Validators\Result\ValidationResult;
use ModularityFrontendForm\DataProcessor\Validators\ValidatorInterface;
use PHPUnit\Framework\TestCase;
use WP_Error;
use WP_REST_Request;

class DataProcessorTest extends TestCase
{
    protected function setUp(): void
    {
        \Brain\Monkey\setUp();
    }

    protected function tearDown(): void
    {
        \Brain\Monkey\tearDown();
    }

    /** @dataProvider validationResults */
    public function testFiltersOnlyValidatedDataBeforeAllHandlers(bool $valid): void
    {
        $input = ['form' => ['field_value' => 'original']];
        $filtered = ['form' => ['field_value' => 'filtered']];
        $request = $this->createMock(WP_REST_Request::class);
        $result = $this->createMock(ValidationResult::class);
        $result->method('getIsValid')->willReturn($valid);
        $result->method('getErrors')->willReturn($valid ? [] : [new WP_Error()]);
        $validated = false;
        $validator = $this->createMock(ValidatorInterface::class);
        $validator->expects($this->once())->method('validate')->with($input, $request)
            ->willReturnCallback(function () use (&$validated, $result) {
                $validated = true;
                return $result;
            });
        if ($valid) {
            \Brain\Monkey\Functions\expect('apply_filters')
                ->once()->with('ModularityFrontendForm/Submission/Data', $input)->andReturnUsing(function () use (&$validated, $filtered) {
                    self::assertTrue($validated);
                    return $filtered;
                });
        } else {
            \Brain\Monkey\Functions\expect('apply_filters')->never();
        }
        $handler = $this->createMock(HandlerInterface::class);
        $handler->expects($valid ? $this->exactly(2) : $this->never())->method('handle')
            ->with($filtered, $request)->willReturn(new HandlerResult());
        $processor = new DataProcessor([$validator], [$handler, $handler], null);
        self::assertSame($valid, $processor->process($input, $request));
        self::assertSame('original', $input['form']['field_value']);
    }

    public static function validationResults(): array
    {
        return [[true], [false]];
    }
}

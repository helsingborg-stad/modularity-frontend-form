<?php

declare(strict_types=1);

namespace ModularityFrontendForm\DataProcessor\Validators;

use AcfService\AcfService;
use ModularityFrontendForm\Config\ConfigInterface;
use ModularityFrontendForm\Config\ModuleConfigInterface;
use ModularityFrontendForm\DataProcessor\Validators\Result\ValidationResultInterface;
use PHPUnit\Framework\TestCase;
use WP_REST_Request;
use WpService\Implementations\FakeWpService;

final class FilesIntegrityCheckTest extends TestCase
{
    public function testRejectsFileWithMissingTemporaryFilename(): void
    {
        $request = $this->createMock(WP_REST_Request::class);
        $request->method('get_file_params')->willReturn(['acf' => [
            'name' => ['field_file' => ['report.pdf']],
            'type' => ['field_file' => ['application/pdf']],
            'tmp_name' => ['field_file' => ['']],
            'error' => ['field_file' => [UPLOAD_ERR_OK]],
            'size' => ['field_file' => [123]],
        ]]);
        $config = $this->createMock(ConfigInterface::class);
        $config->method('getFieldNamespace')->willReturn('acf');
        $wp = new FakeWpService(['__' => static fn(string $text): string => $text]);
        $validationResult = $this->createMock(ValidationResultInterface::class);
        $validationResult->expects(self::once())->method('setError')->with(
            self::isInstanceOf(\WP_Error::class),
        );
        $validator = new FilesIntegrityCheck(
            $wp,
            $this->createMock(AcfService::class),
            $config,
            $this->createMock(ModuleConfigInterface::class),
            $validationResult,
        );

        self::assertSame($validationResult, $validator->validate([], $request));
    }
}

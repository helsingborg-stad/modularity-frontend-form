<?php

declare(strict_types=1);

namespace ModularityFrontendForm\DataProcessor\Handlers;

use AcfService\AcfService;
use ModularityFrontendForm\Config\ConfigInterface;
use ModularityFrontendForm\Config\ModuleConfigInterface;
use ModularityFrontendForm\DataProcessor\FileHandlers\FileHandlerInterface;
use PHPUnit\Framework\TestCase;
use WP_REST_Request;
use WpService\Implementations\FakeWpService;

final class WpDbHandlerTest extends TestCase
{
    public function testStoresImageAsOneAttachmentIdAndFileAsAttachmentIdArray(): void
    {
        $files = $this->createMock(FileHandlerInterface::class);
        $files->expects(self::once())->method('handle')->willReturn([
            'field_image' => [['id' => 101]],
            'field_files' => [['id' => 102], ['id' => 103]],
        ]);
        $acf = $this->createMock(AcfService::class);
        $acf->method('getFieldObject')->willReturnCallback(static fn(string $key): array => [
            'type' => $key === 'field_image' ? 'image' : 'file',
        ]);
        $acf->expects(self::exactly(2))->method('updateField')->withConsecutive(
            ['field_image', 101, 99],
            ['field_files', [102, 103], 99],
        )->willReturn(true);

        $config = $this->createMock(ConfigInterface::class);
        $config->method('getFieldNamespace')->willReturn('acf');
        $config->method('getMetaDataNamespace')->willReturn('meta');
        $config->method('getAllowedHtmlTags')->willReturn([]);
        $module = $this->createMock(ModuleConfigInterface::class);
        $module->method('getModuleId')->willReturn(1);
        $module->method('getModuleTitle')->willReturn('Submission');
        $module->method('getWpDbHandlerConfig')->willReturn((object) [
            'saveToPostType' => 'submission',
            'saveToPostTypeStatus' => 'draft',
        ]);
        $wp = new FakeWpService([
            'wpInsertPost' => 99,
            'wpGeneratePassword' => 'password',
            'isWpError' => false,
            'sanitizeTextField' => static fn(string $value): string => $value,
        ]);
        $params = (object) ['holdingPostId' => 7];
        $handler = new WpDbHandler($wp, $acf, $config, $module, $params, fileHandler: $files);
        $request = $this->createMock(WP_REST_Request::class);

        self::assertTrue($handler->handle([
            'acf' => [
                'field_image' => 'original-image-value',
                'field_files' => 'original-file-value',
            ],
        ], $request)?->isOk());
    }
}

<?php

namespace ModularityFrontendForm\Api\Submit;

use AcfService\Implementations\FakeAcfService;
use ModularityFrontendForm\Config\NullConfig;
use ModularityFrontendForm\DataProcessor\Handlers\HandlerFactory;
use ModularityFrontendForm\DataProcessor\Validators\ValidatorFactory;
use PsrLogger\NullLoggerFactory;
use PHPUnit\Framework\TestCase;
use WpService\Implementations\FakeWpService;

class PostTest extends TestCase {

    /**
     * @testdox class can be instantiated
     */
    public function testClassCanBeInstantiated() {
        $moduleConfigFactory = $this->createMock(\ModularityFrontendForm\Config\ModuleConfigFactoryInterface::class);
        $validationFactory = $this->createMock(ValidatorFactory::class);
        $handlerFactory = $this->createMock(HandlerFactory::class);
        $this->assertInstanceOf( Post::class, new Post(new FakeWpService(), new FakeAcfService(), new NullConfig(), $moduleConfigFactory, $validationFactory, $handlerFactory, new NullLoggerFactory()) );
    }
}
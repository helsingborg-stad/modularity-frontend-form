<?php

namespace ModularityFrontendForm\Api\Submit;

use AcfService\Implementations\FakeAcfService;
use ModularityFrontendForm\Config\NullConfig;
use PHPUnit\Framework\TestCase;
use WpService\Implementations\FakeWpService;

class PostTest extends TestCase {

    /**
     * @testdox class can be instantiated
     */
    public function testClassCanBeInstantiated() {
        $moduleConfigFactory = $this->createMock(\ModularityFrontendForm\Config\ModuleConfigFactoryInterface::class);
        $this->assertInstanceOf( Post::class, new Post(new FakeWpService(), new FakeAcfService(), new NullConfig(), $moduleConfigFactory) );
    }
}
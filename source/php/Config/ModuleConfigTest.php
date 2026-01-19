<?php
namespace ModularityFrontendForm\Config;

use AcfService\Implementations\FakeAcfService;
use ModularityFrontendForm\Config\ConfigInterface;
use PHPUnit\Framework\TestCase;
use WpService\Implementations\FakeWpService;
use WpService\WpService;

class ModuleConfigTest extends TestCase
{
  protected function setUp(): void
  {
    if(!defined('OBJECT')) {
      define('OBJECT', 'object');
    }
    parent::setUp();
  }

  /**
   * @testdox throws exception if module not found
   */
  public function testCanBeInstantiated(): void {

    try {
      new ModuleConfig(
        new FakeWpService(),
        new FakeAcfService(),
        new NullConfig(),
        1
      );
    } catch (\Exception $e) {
      $this->assertEquals('Module not found', $e->getMessage());
      return;
    }
    $this->fail('Expected exception not thrown');
  }

  /**
   * @testdox throws exception if module is not of correct type
   */
  public function testThrowsExceptionIfModuleIsNotOfCorrectType(): void {
    $wpService = new FakeWpService([
      'getPost' => new \WP_Post([]),
      'getPostType' => 'wrong-type'
    ]);

    try {
      new ModuleConfig(
        $wpService,
        new FakeAcfService(),
        new class extends NullConfig implements ConfigInterface {
          public function getModuleSlug(): string {
            return 'correct-type';
          }
        },
        1
      );
    } catch (\Exception $e) {
      $this->assertEquals('Module is not of type correct-type', $e->getMessage());
      return;
    }
    $this->fail('Expected exception not thrown');
  }

  /**
   * @testdox getModuleId returns provided module ID
   */
  public function testGetModuleIdReturnsProvidedModuleId(): void {
    $wpService = self::createBasicWpService();
    $sut = new ModuleConfig(
      $wpService,
      new FakeAcfService(),
      new class extends NullConfig implements ConfigInterface {
        public function getModuleSlug(): string {
          return 'test-module';
        }
      },
      42
    );

    static::assertSame(42, $sut->getModuleId());
  }

  /**
   * @testdox getModuleIsEditable returns true
   */
  public function testGetModuleIsEditableReturnsTrue(): void {
    $wpService = self::createBasicWpService();
    $sut = new ModuleConfig(
      $wpService,
      new FakeAcfService(),
      new class extends NullConfig implements ConfigInterface {
        public function getModuleSlug(): string {
          return 'test-module';
        }
      },
      42
    );

    static::assertTrue($sut->getModuleIsEditable());
  }

  /**
   * @testdox getModuleSlug returns correct slug
   */
  public function testGetModuleSlugReturnsCorrectSlug(): void {
    $wpService = self::createBasicWpService();
    $sut = new ModuleConfig(
      $wpService,
      new FakeAcfService(),
      new class extends NullConfig implements ConfigInterface {
        public function getModuleSlug(): string {
          return 'test-module';
        }
      },
      42
    );

    static::assertSame('test-module', $sut->getModuleSlug());
  }

  /**
   * @testdox getModuleTitle returns correct title
   */
  public function testGetModuleTitleReturnsCorrectTitle(): void {
    $post = new \WP_Post([]);
    $post->post_title = 'Test Module Title';
    $wpService = new FakeWpService([
      'getPost' => $post,
      'getPostType' => 'test-module'
    ]);
    $sut = new ModuleConfig(
      $wpService,
      new FakeAcfService(),
      new class extends NullConfig implements ConfigInterface {
        public function getModuleSlug(): string {
          return 'test-module';
        }
      },
      42
    );

    static::assertSame('Test Module Title', $sut->getModuleTitle());
  }

  /**
   * @testdox getActivatedHandlers returns active handlers from acf
   */
  public function testGetActivatedHandlersReturnsActiveHandlersFromAcf(): void {
    $wpService = self::createBasicWpService();
    $acfService = new FakeAcfService([
      'getField' => fn($field) => $field === 'activeHandlers' ? ['handler1', 'handler2'] : null
    ]);
    $sut = new ModuleConfig(
      $wpService,
      $acfService,
      new class extends NullConfig implements ConfigInterface {
        public function getModuleSlug(): string {
          return 'test-module';
        }
      },
      42
    );

    static::assertSame(['handler1', 'handler2'], $sut->getActivatedHandlers());
  }

  private static function createBasicWpService(): WpService {
    return new FakeWpService([
      'getPost' => new \WP_Post([]),
      'getPostType' => 'test-module'
    ]);
  }

}
<?php

namespace ModularityFrontendForm\Api\Submit;

use PHPUnit\Framework\TestCase;
use WP_Error;
use WpService\Implementations\FakeWpService;

class PostTest extends TestCase {

    /**
     * @testdox class can be instantiated
     */
    public function testClassCanBeInstantiated() {
        $this->assertInstanceOf( Post::class, new Post(new FakeWpService()) );
    }

    /**
     * @testdox insertPost() returns an integer if successful
     */
    public function testInsertPostReturnsIntegerIfSuccessful() {
        $fakeService = new FakeWpService(['wpInsertPost' => 123, 'isWpError' => false]);
        $post = new Post($fakeService);
        
        $result = $post->insertPost(1, ['meta_key' => 'meta_value']);
        
        $this->assertIsInt($result);
    }

    /**
     * @testdox insertPost() returns a WP_Error if unsuccessful
     */
    public function testInsertPostReturnsWpErrorIfUnsuccessful() {
        $fakeService = new FakeWpService(['wpInsertPost' => new WP_Error('error_code', 'error_message'), 'isWpError' => true]);
        $post = new Post($fakeService);
        
        $result = $post->insertPost(1, ['meta_key' => 'meta_value']);
        
        $this->assertInstanceOf(WP_Error::class, $result);
    }
}
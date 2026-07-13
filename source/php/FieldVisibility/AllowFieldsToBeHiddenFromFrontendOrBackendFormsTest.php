<?php

namespace ModularityFrontendForm\FieldVisibility;

use PHPUnit\Framework\TestCase;
use WP_Post;
use WpService\Contracts\AddFilter;
use WpService\Contracts\GetPostType;
use WpService\Contracts\IsAdmin;

class AllowFieldsToBeHiddenFromFrontendOrBackendFormsTest extends TestCase {

    protected function tearDown(): void
    {
        // Reset global variables that might have been modified during tests
        global $pagenow;
        $pagenow = null;
        $_GET = [];
    }

    /**
     * @testdoxt if a field is set to be hidden from the frontend form, it should not be visible in the frontend form, but still be visible in the backend form.
     */
    public function testFieldIsHiddenFromFrontendForm(): void {
        $fields = [
            [
                'name' => 'field_1',
                'is_publicly_hidden' => 1,
                'is_privately_hidden' => 0
            ],
            [
                'name' => 'field_2',
                'is_publicly_hidden' => 0,
                'is_privately_hidden' => 1
            ],
            [
                'name' => 'field_3',
                'is_publicly_hidden' => 0,
                'is_privately_hidden' => 0
            ]
        ];

        // Act
        $sut = new AllowFieldsToBeHiddenFromFrontendOrBackendForms(static::createWpService(isAdmin: false, postType: false));
        $filtered = $sut->filterByVisibility($fields);

        // Assert
        static::assertCount(2, $filtered);
        static::assertEquals('field_2', $filtered[0]['name']);
        static::assertEquals('field_3', $filtered[1]['name']);
    }

    /**
     * @testdoxt if a field is set to be hidden from the backend form, it should not be visible in the backend form, but still be visible in the frontend form.
     */
    public function testFieldIsHiddenFromBackendForm(): void {
        $fields = [
            [
                'name' => 'field_1',
                'is_publicly_hidden' => 1,
                'is_privately_hidden' => 0
            ],
            [
                'name' => 'field_2',
                'is_publicly_hidden' => 0,
                'is_privately_hidden' => 1
            ],
            [
                'name' => 'field_3',
                'is_publicly_hidden' => 0,
                'is_privately_hidden' => 0
            ]
        ];

        // Act
        $sut = new AllowFieldsToBeHiddenFromFrontendOrBackendForms(static::createWpService(isAdmin: true, postType: false));
        $filtered = $sut->filterByVisibility($fields);

        // Assert
        static::assertCount(2, $filtered);
        static::assertEquals('field_1', $filtered[0]['name']);
        static::assertEquals('field_3', $filtered[1]['name']);
    }

    /**
     * @testdox when on the ACF field group edit screen, fields should not be hidden regardless of their visibility settings, to allow the user to edit the field settings.
      */
    public function testFieldsAreNotHiddenOnAcfFieldGroupEditScreen(): void {
        $fields = [
            [
                'name' => 'field_1',
                'is_publicly_hidden' => 1,
                'is_privately_hidden' => 1
            ],
            [
                'name' => 'field_2',
                'is_publicly_hidden' => 0,
                'is_privately_hidden' => 0
            ]
        ];

        // Act
        $GLOBALS['pagenow'] = 'post.php';
        $_GET['post'] = '123';
        $sut = new AllowFieldsToBeHiddenFromFrontendOrBackendForms(static::createWpService(isAdmin: true, postType: 'acf-field-group'));
        $filtered = $sut->filterByVisibility($fields);

        // Assert
        static::assertCount(2, $filtered);
        static::assertEquals('field_1', $filtered[0]['name']);
        static::assertEquals('field_2', $filtered[1]['name']);
    }

    /**
     * @testdox when saving an ACF field group, fields should not be hidden regardless of their visibility settings, to allow the user to edit the field settings.
    */
    public function testFieldsAreNotHiddenWhenSavingAcfFieldGroup(): void {
        $fields = [
            [
                'name' => 'field_1',
                'is_publicly_hidden' => 1,
                'is_privately_hidden' => 1
            ],
            [
                'name' => 'field_2',
                'is_publicly_hidden' => 0,
                'is_privately_hidden' => 0
            ]
        ];

        // Act
        $GLOBALS['pagenow'] = 'post.php';
        $_POST['post_ID'] = '123';
        $sut = new AllowFieldsToBeHiddenFromFrontendOrBackendForms(static::createWpService(isAdmin: true, postType: 'acf-field-group'));
        $filtered = $sut->filterByVisibility($fields);

        // Assert
        static::assertCount(2, $filtered);
        static::assertEquals('field_1', $filtered[0]['name']);
        static::assertEquals('field_2', $filtered[1]['name']);
    }

    private static function createWpService(bool $isAdmin = false, string|false $postType = false):AddFilter&IsAdmin&GetPostType {
        return new class($isAdmin, $postType) implements AddFilter, IsAdmin, GetPostType {

            public function __construct(private bool $isAdmin, private string|false $postType)
            {
            }

            public function addFilter(string $hookName, callable $callback, int $priority = 10, int $acceptedArgs = 1): true
            {
                return true;
            }

            public function isAdmin(): bool
            {
                return $this->isAdmin;
            }

            public function getPostType(int|WP_Post|null $post = null): string|false
            {
                return $this->postType;
            }
        };
    }
}
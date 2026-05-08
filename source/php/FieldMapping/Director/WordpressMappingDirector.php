<?php 

namespace ModularityFrontendForm\FieldMapping\Director;

use ModularityFrontendForm\Config\Config;
use WpService\WpService;

use ModularityFrontendForm\FieldMapping\Director\FieldMappingDirectorInterface;
use ModularityFrontendForm\FieldMapping\Mapper\Interfaces\FieldMapperInterface;
use ModularityFrontendForm\FieldMapping\Mapper\Wordpress\PostContent;
use ModularityFrontendForm\FieldMapping\Mapper\Wordpress\PostTitle;

class WordpressMappingDirector implements FieldMappingDirectorInterface
{

    /**
     * @var WpService
     */
    public function __construct(
        protected WpService $wpService,
        protected object $lang,
        protected Config $config
    ) {
    }

    /**
     * @var array
     */
    protected array $mapperMap = [
        'post_title' => PostTitle::class,
        'post_content' => PostContent::class,
    ];

    /**
     * Resolves the appropriate field mapper based on the field type.
     *
     * @param string $field The field configuration.
     * @return FieldMapperInterface The resolved field mapper instance.
     * @throws \RuntimeException If the mapper class is not valid.
     */
    public function resolveMapper(mixed $field): FieldMapperInterface
    {
        $mapperClass = $this->mapperMap[$field] ?? null;
        if (empty($mapperClass) || !is_subclass_of($mapperClass, FieldMapperInterface::class)) {
            throw new \RuntimeException("Mapper class for field type '{$field}' is not valid.");
        }

        return $mapperClass::getInstance($field, $this->wpService, $this->lang, $this->config);
    }
}
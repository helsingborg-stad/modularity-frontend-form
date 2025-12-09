<?php 

namespace ModularityFrontendForm\FieldMapping\Director;

use ModularityFrontendForm\Config\Config;
use WpService\WpService;

use ModularityFrontendForm\FieldMapping\Director\FieldMappingDirectorInterface;
use ModularityFrontendForm\FieldMapping\Mapper\Interfaces\FieldMapperInterface;

use ModularityFrontendForm\FieldMapping\Mapper\Acf\TextFieldMapper;
use ModularityFrontendForm\FieldMapping\Mapper\Acf\EmailFieldMapper;
use ModularityFrontendForm\FieldMapping\Mapper\Acf\UrlFieldMapper;
use ModularityFrontendForm\FieldMapping\Mapper\Acf\TextareaFieldMapper;
use ModularityFrontendForm\FieldMapping\Mapper\Acf\TrueFalseFieldMapper;
use ModularityFrontendForm\FieldMapping\Mapper\Acf\SelectFieldMapper;
use ModularityFrontendForm\FieldMapping\Mapper\Acf\CheckboxFieldMapper;
use ModularityFrontendForm\FieldMapping\Mapper\Acf\MessageFieldMapper;
use ModularityFrontendForm\FieldMapping\Mapper\Acf\FileFieldMapper;
use ModularityFrontendForm\FieldMapping\Mapper\Acf\NumberFieldMapper;
use ModularityFrontendForm\FieldMapping\Mapper\Acf\ImageFieldMapper;
use ModularityFrontendForm\FieldMapping\Mapper\Acf\RadioFieldMapper;
use ModularityFrontendForm\FieldMapping\Mapper\Acf\RepeaterFieldMapper;
use ModularityFrontendForm\FieldMapping\Mapper\Acf\TimePickerFieldMapper;
use ModularityFrontendForm\FieldMapping\Mapper\Acf\DatePickerFieldMapper;
use ModularityFrontendForm\FieldMapping\Mapper\Acf\ButtonGroupFieldMapper;
use ModularityFrontendForm\FieldMapping\Mapper\Acf\ErrorFieldMapper;
use ModularityFrontendForm\FieldMapping\Mapper\Acf\GalleryFieldMapper;
use ModularityFrontendForm\FieldMapping\Mapper\Acf\GoogleMapFieldMapper;
use ModularityFrontendForm\FieldMapping\Mapper\Acf\TaxonomyFieldMapper;
use ModularityFrontendForm\FieldMapping\Mapper\Acf\WysiwygFieldMapper;

class FieldMappingDirector implements FieldMappingDirectorInterface
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
        'text'          => TextFieldMapper::class,
        'email'         => EmailFieldMapper::class,
        'url'           => UrlFieldMapper::class,
        'textarea'      => TextareaFieldMapper::class,
        'true_false'    => TrueFalseFieldMapper::class,
        'select'        => SelectFieldMapper::class,
        'checkbox'      => CheckboxFieldMapper::class,
        'message'       => MessageFieldMapper::class,
        'file'          => FileFieldMapper::class,
        'number'        => NumberFieldMapper::class,
        'image'         => ImageFieldMapper::class,
        'radio'         => RadioFieldMapper::class,
        'repeater'      => RepeaterFieldMapper::class,
        'time_picker'   => TimePickerFieldMapper::class,
        'date_picker'   => DatePickerFieldMapper::class,
        'button_group'  => ButtonGroupFieldMapper::class,
        'google_map'    => GoogleMapFieldMapper::class,
        'taxonomy'      => TaxonomyFieldMapper::class,
        'gallery'       => GalleryFieldMapper::class,
        'wysiwyg'       => WysiwygFieldMapper::class
    ];

    /**
     * Resolves the appropriate field mapper based on the field type.
     *
     * @param array $field The field configuration.
     * @return FieldMapperInterface The resolved field mapper instance.
     * @throws \RuntimeException If the mapper class is not valid.
     */
    public function resolveMapper(mixed $field): FieldMapperInterface
    {
        $type = $field['type'] ?? 'text';
        $mapperClass = $this->mapperMap[$type] ?? null;

        if (empty($mapperClass) || !is_subclass_of($mapperClass, FieldMapperInterface::class)) {
            return new ErrorFieldMapper($field, $this->wpService, $this->lang, $this->config);
        }

        return $mapperClass::getInstance($field, $this->wpService, $this->lang, $this->config);
    }
}
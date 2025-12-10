<?php 
namespace ModularityFrontendForm\Api\Read;

enum GetReturnTypeEnum: string
{
    // Generic value-or-array types
    case ARRAY     = 'array';
    case VALUE     = 'value';

    // ID-based formats
    case ID        = 'id';

    // URL-based formats
    case URL       = 'url';

    // Object formats (WP_Post, WP_Term)
    case OBJECT    = 'object';

    // WYSIWYG-specific
    case HTML      = 'html';
    case TEXTAREA  = 'textarea';

    // Date/time formats
    case STRING    = 'string';
    case TIMESTAMP = 'timestamp';
}
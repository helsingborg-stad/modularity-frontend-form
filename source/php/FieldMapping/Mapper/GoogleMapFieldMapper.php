<?php

namespace ModularityFrontendForm\FieldMapping\Mapper;

use ModularityFrontendForm\FieldMapping\Mapper\Interfaces\FieldMapperInterface;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperConstruct;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperGetInstance;

class GoogleMapFieldMapper implements FieldMapperInterface
{
    use FieldMapperConstruct;
    use FieldMapperGetInstance;

    public function map(): ?array
    {
        $mapped = (new BasicFieldMapper($this->field, 'googleMap'))->map();

        $mapped['height'] = $this->field['height'] ?: '400';
        $mapped['lat'] = $this->field['center_lat'] ?: '59.32932';
        $mapped['lng'] = $this->field['center_lng'] ?: '18.06858';
        $mapped['zoom'] = $this->field['zoom'] ?: '14';

        $mapped['attributeList']['style'] = sprintf(
            'height: %spx; position: relative',
            $mapped['height']
        );

        return $mapped;
    }
}
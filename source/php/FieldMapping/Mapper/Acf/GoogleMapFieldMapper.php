<?php

namespace ModularityFrontendForm\FieldMapping\Mapper\Acf;

use ModularityFrontendForm\FieldMapping\Mapper\Interfaces\FieldMapperInterface;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperConstruct;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperGetInstance;

class GoogleMapFieldMapper implements FieldMapperInterface
{
    use FieldMapperConstruct;
    use FieldMapperGetInstance;

    public function map(): array
    {
        $mapped = (new BasicFieldMapper($this->field, $this->lang, 'googleMap'))->map();

        if ($mapped['required']) {
            $mapped['attributeList']['data-js-required'] = 'true';
            unset($mapped['required']);
        }

        $mapped['height'] = $this->field['height'] ?: '400';
        $mapped['lat'] = $this->field['center_lat'] ?: '59.32932';
        $mapped['lng'] = $this->field['center_lng'] ?: '18.06858';
        $mapped['zoom'] = $this->field['zoom'] ?: '14';

        // Prefer Municipio's site-wide defaults when configured in Customizer.
        $center = $this->wpService->getThemeMod('map_start_lat_lng', '');
        if (is_string($center)) {
            $coordinates = array_map('trim', explode(',', $center));
            if (
                count($coordinates) === 2
                && is_numeric($coordinates[0])
                && is_numeric($coordinates[1])
                && abs((float) $coordinates[0]) <= 90
                && abs((float) $coordinates[1]) <= 180
            ) {
                $mapped['lat'] = (float) $coordinates[0];
                $mapped['lng'] = (float) $coordinates[1];
            }
        }

        $zoom = $this->wpService->getThemeMod('map_start_zoom', null);
        if (is_numeric($zoom) && (float) $zoom >= 0 && (float) $zoom <= 18) {
            $mapped['zoom'] = (int) $zoom;
        }
        $mapped['classList'][] = 'mod-frontend-form__openstreetmap';
        // openstreetmap class is needed
        $mapped['classList'][] = 'openstreetmap';
        $mapped['classList'][] = 'c-field';

        $mapped['attributeList']['style'] = sprintf(
            'min-height: %spx; position: relative',
            $mapped['height']
        );

        return $mapped;
    }
}

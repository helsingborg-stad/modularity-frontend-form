<?php

namespace ModularityFrontendForm\FieldMapping\Mapper\Acf;

use ModularityFrontendForm\Config\Config;
use PHPUnit\Framework\TestCase;
use WpService\WpService;

class GoogleMapFieldMapperTest extends TestCase
{
    /** @dataProvider mapDefaults */
    public function testMapDefaults($center, $zoom, array $expected): void
    {
        $wpService = $this->createMock(WpService::class);
        $wpService->method('getThemeMod')->willReturnCallback(
            fn($name, $default = false) => [
                'map_start_lat_lng' => $center,
                'map_start_zoom' => $zoom,
            ][$name] ?? $default
        );
        $field = [
            'key' => 'map', 'label' => 'Place', 'required' => false,
            'instructions' => '', 'conditional_logic' => false,
            'wrapper' => ['id' => '', 'class' => '', 'width' => ''],
            'height' => 400, 'center_lat' => '59.32932',
            'center_lng' => '18.06858', 'zoom' => '12',
        ];
        $mapper = new GoogleMapFieldMapper(
            $field, $wpService, (object) ['errorRequired' => 'Required'],
            new Config($wpService, 'test')
        );
        $mapped = $mapper->map();
        self::assertEquals($expected, [$mapped['lat'], $mapped['lng'], $mapped['zoom']]);
    }

    public function mapDefaults(): array
    {
        return [
            'customizer overrides field defaults' => ['55.84, 13.30', 14, [55.84, 13.30, 14]],
            'missing settings retain field defaults' => ['', null, [59.32932, 18.06858, 12]],
            'invalid settings retain field defaults' => ['invalid', 'invalid', [59.32932, 18.06858, 12]],
            'out of range settings retain field defaults' => ['91, 181', 19, [59.32932, 18.06858, 12]],
            'partial center retains both field coordinates' => ['55.84', 14, [59.32932, 18.06858, 14]],
            'zero coordinates and zoom are valid' => ['0, 0', 0, [0, 0, 0]],
        ];
    }
}

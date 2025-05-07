@element([
    'classList' => [
        'openstreetmap'
    ],
    'attributeList' => $field['attributeList'] ?? [],
])
    @field([
        'type' => 'hidden',
        'name' => $field['name'],
        'required' => $field['required'] ?? false,
    ])
    @endfield
    @element([
        'id' => $field['name'],
        'classList' => ['mod-frontend-form__openstreetmap'],
        'attributeList' => [
            'data-js-openstreetmap' => 'true',
            'data-js-zoom' => $field['zoom'],
            'data-js-lat' => $field['lat'],
            'data-js-lng' => $field['lng'],
            'style' => 'height: ' . $field['height'] . 'px; position: relative;',
        ]
    ])
        <!-- osm -->
    @endelement
@endelement
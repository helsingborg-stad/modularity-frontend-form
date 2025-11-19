@element([
    'classList' => $field['classList'],
    'attributeList' => $field['attributeList'],
    'id' => $field['id']
])
   @if (!empty($field['label']))
        @typography([
            'element' => 'span',
            'classList' => [
                'mod-frontend-form__custom-label'
            ],
        ])
            {{ $field['label'] }} {!! $field['required'] ? '<span class="u-color__text--danger">*</span>' : '' !!}
        @endtypography
    @endif
    @field([
        'type' => 'hidden',
        'attributeList' => [
            'data-js-google-map-hidden-field' => true
        ],
        'required' => $field['required'] ?? false,
        'disabled' => true,
        'name' => $field['name']
    ])
    @endfield
    @element([
        'id' => $field['name'] . '_map',
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
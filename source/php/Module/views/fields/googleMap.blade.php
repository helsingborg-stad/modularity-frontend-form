@element([
    'classList' => $field['classList'],
    'attributeList' => $field['attributeList'],
    'id' => $field['id']
])
   @if (!empty($field['label']))
        @typography([
            'element' => 'span',
            'classList' => [
                'c-field__label'
            ],
        ])
            {{ $field['label'] }} {!! !empty($field['attributeList']['data-js-required']) ? '<span class="u-color__text--danger">*</span>' : '' !!}
        @endtypography
    @endif
    @field([
        'type' => 'hidden',
        'attributeList' => [
            'data-js-google-map-hidden-field' => true
        ],
        'disabled' => true,
        'name' => $field['name']
    ])
    @endfield
    @element([
        'classList' => ['mod-frontend-form__openstreetmap_container'],
    ])
        @map([
            'id' => $field['name'] . '_map',
            'lat' => $field['lat'],
            'lng' => $field['lng'],
            'zoom' => $field['zoom'],
            'provider' => 'openstreetmap',
            'mapStyle' => $field['mapStyle'],
            'height' => $field['height'] . 'px',
        ])
        @endmap
    @endelement
@endelement
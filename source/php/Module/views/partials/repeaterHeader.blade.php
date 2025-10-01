@element([
    'classList' => [
        'mod-frontend-form__repeater-header'
    ],
])
    @typography([
        'element' => 'h2',
        'variant' => 'h4',
        'classList' => [
            'u-margin__top--0'
        ],
    ])
        {{ $field['label'] }}
    @endtypography
    @if(isset($field['min'], $field['max']))
        @element([
            'classList' => [
                'mod-frontend-form__repeater-min-max'
            ]
        ])
            @element([
                'componentElement' => 'span',
                'attributeList' => [
                    'data-js-repeater-row-counter' => 'true'
                ]
            ])
                {!! "&nbsp;" . (string) $field['min'] !!}
            @endelement
            &nbsp;/&nbsp;
            @element([
                'componentElement' => 'span'
            ])
                {{ $field['max'] }}
            @endelement
        @endelement
    @endif
@endelement
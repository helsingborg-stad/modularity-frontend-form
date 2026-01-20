@element([
    'classList' => [
        'c-field',
        'mod-frontend-form__repeater-header'
    ],
])
    @typography([
        'element' => 'div',
        'variant' => 'label',
        'classList' => [
            'c-field__label',
            'u-margin--0'
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
                <!-- min -->
                {{ $field['min'] }}
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
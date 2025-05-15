@if (!empty($field['fields']))
    @element([
        'classList' => [
            'mod-frontend-form__repeater'
        ],
        'attributeList' => $field['attributeList'] ?? [],
    ])
        @typography([
            'element' => 'h2',
            'variant' => 'h4',
        ])
            {{ $field['label'] }}
        @endtypography
        @element([
            'classList' => [
                'mod-frontend-form__repeater-container'
            ],
            'attributeList' => [
                'data-js-repeater-row-container' => 'true'
            ]
        ])
            @element([
                'componentElement' => 'template',
                'attributeList' => [
                    'data-js-repeater-row-template' => 'true',
                ]
            ])
                @element([
                    'classList' => [
                        'mod-frontend-form__repeater-row'
                    ],
                    'attributeList' => [
                        'data-js-repeater-row' => 'true'
                    ]
                ])
                    @element([
                        'attributeList' => [
                            'data-js-repeater-row-fields' => 'true'
                        ],
                        'classList' => [
                            'mod-frontend-form__repeater-row-fields'
                        ]
                    ])
                        @foreach ($field['fields'] as $subField)
                            @includeIf('fields.' . $subField['view'], ['field' => $subField])
                        @endforeach
                    @endelement
                        @button([
                            'style' => 'filled',
                            'icon' => 'remove',
                            'style' => 'basic',
                            'color' => 'primary',
                            'size' => 'md',
                            'classList' => [
                                'mod-frontend-form__repeater-row-remove',
                            ],
                            'attributeList' => [
                                'aria-label' => $lang->removeRow ?? 'Remove row',
                                'data-js-repeater-remove-row' => 'true'
                            ]
                        ])
                        @endbutton
                @endelement
            @endelement
        @endelement
        @button([
            'text' => 'New row [+]', // TODO: Translation
            'color' => 'primary',
            'style' => 'filled',
            'attributeList' => [
                'data-js-repeater-add-row' => 'true'
            ],
            'classList' => [
                'u-margin__top--2'
            ]
        ])
        @endbutton
    @endelement
@endif
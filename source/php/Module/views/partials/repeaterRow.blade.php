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
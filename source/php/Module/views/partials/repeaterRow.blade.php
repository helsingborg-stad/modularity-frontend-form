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
            'mod-frontend-form__repeater-row-fields',
            'o-layout-grid',
            'o-layout-grid--cols-12',
            'o-layout-grid--row-gap-8',
            'o-layout-grid--column-gap-6'
        ]
    ])
        @foreach ($field['fields'] as $index => $subField)
            @includeIf('fields.' . $subField['view'], ['field' => $subField])
        @endforeach
    @endelement
        @button([
            'style' => 'basic',
            'icon' => 'delete',
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
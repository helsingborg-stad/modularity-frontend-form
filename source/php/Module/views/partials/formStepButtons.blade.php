@element([
    'classList' => [
        'mod-frontend-form__step-buttons'
    ]
])
    @button([
        'style' => 'filled',
        'color' => 'default',
        'attributeList' => [
            'data-js-frontend-form-previous-step' => 'true'
        ],
        'classList' => [
            'u-visibility--hidden'
        ],
        'text' => $lang->previous,
    ])
    @endbutton
    @button([
        'style' => 'filled',
        'color' => 'primary',
        'attributeList' => [
            'data-js-frontend-form-next-step' => 'true'
        ],
        'text' => $lang->next,
    ])
    @endbutton
@endelement
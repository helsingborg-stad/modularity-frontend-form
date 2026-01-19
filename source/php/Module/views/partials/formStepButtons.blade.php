@element([
    'classList' => [
        'mod-frontend-form__step-buttons'
    ]
])
    @button([
        'style' => 'filled',
        'color' => 'default',
        'icon' => 'arrow_back_ios',
        'reversePositions' => true,
        'attributeList' => [
            'data-js-frontend-form-previous-step' => 'true'
        ],
        'classList' => [
            'u-visibility--hidden',
            'u-margin__right--auto'
        ],
        'text' => $lang->previous,
    ])
    @endbutton

    @button([
        'style' => 'filled',
        'color' => 'primary',
        'icon' => $stepsCount === 1 ? 'send' : 'arrow_forward_ios',
        'attributeList' => [
            'data-js-frontend-form-next-step' => 'true'
        ],
        'text' => $stepsCount === 1 ? $lang->submit : $lang->next,
    ])
    @endbutton
@endelement
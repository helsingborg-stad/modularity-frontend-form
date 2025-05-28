@element(['classList' => [
    'mod-frontend-form-working',
    'u-rounded--16',
    'u-display--none'
    ],
    'attributeList' => [
        'data-js-frontend-form-working' => 'true'
    ]
])

    @icon([
        'icon' => 'send',
        'size' => 'xxl',
        'classList' => [
            'mod-frontend-form-working__icon'
        ],
        'attributeList' => [
            'data-js-frontend-form-working__icon' => 'true'
        ]
    ])
    @endicon

    @progressBar([
        'classList' => [
            'mod-frontend-form-working__progress-bar'
        ],
        'attributeList' => [
            'data-js-frontend-form-working__progress' => 'true'
        ],
        'value' => 25,
    ])
    @endprogressBar

    @element(['classList' => [
        'u-display--flex',
        'u-flex--column',
        'u-align-items--center',
        'u-justify-content--center',
        'u-flex-direction--column',
        'u-gap--1']
    ])
        @typography([
            'element' => 'div',
            'variant' => 'body',
            'classList' => [
                'mod-frontend-form-working__title'
            ],
            'attributeList' => [
                'data-js-frontend-form-working__title' => 'true'
            ],
        ])
            {{ $lang->submitting ?? 'Submitting'}}
        @endtypography
        
        @typography([
            'element' => 'div',
            'variant' => 'meta',
            'classList' => [
                'mod-frontend-form-working__description'
            ],
            'attributeList' => [
                'data-js-frontend-form-working__description' => 'true'
            ],
        ])
            {{ $lang->submitInit ?? 'Preparing' }}
        @endtypography
    @endelement
    @element([
        'classList' => [
            'mod-frontend-form-working__button'
        ]
    ])
        @button([
            'style' => 'outlined',
            'color' => 'secondary',
            'size' => 'md',
            'classList' => [
                'u-display--none'
            ],
            'attributeList' => [
                'data-js-frontend-form-working-return-button' => 'true'
            ],
            'text' => $lang->return ?? 'Return'
        ])
        @endbutton
        @button([
            'style' => 'filled',
            'color' => 'secondary',
            'size' => 'md',
            'classList' => [
                'u-display--none'
            ],
            'attributeList' => [
                'data-js-frontend-form-working-try-again-button' => 'true'
            ],
            'text' => $lang->tryAgain ?? 'Try again'
        ])
        @endbutton
    @endelement
@endelement
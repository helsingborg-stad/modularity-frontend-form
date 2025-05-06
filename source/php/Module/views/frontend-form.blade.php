@element([
    'classList' => [
        'mod-frontend-form'
    ],
    'attributeList' => [
        'data-js-frontend-form' => 'true'
    ]
])

    @includeWhen(empty($hideTitle) && !empty($postTitle), 'partials.module-title')

    @form([
        'attributeList' => [
            'data-js-frontend-form-id' => $moduleId,
        ],
        'classList' => [
            'mod-frontend-form__form'
        ],
    ])

        @element(['classList' => ['u-position--relative']])

            @include('partials.working')

            @foreach($steps as $index => $step)
                @include('step', ['step' => $step, 'index' => $index])
            @endforeach

        @endelement

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
    @endform
@endelement
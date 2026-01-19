@card([
    'classList' => [
        'mod-frontend-form',
        'o-layout-grid',
        'o-layout-grid--cq',
        'o-layout-grid--gap-12',
        'u-level-3',
        'c-card--panel'
    ],
    'attributeList' => [
        'data-js-frontend-form' => 'true'
    ]
])

    @include('partials.module-title')
    @include('partials.working')

    @element([
        'classList' => [
            'mod-frontend-form__layout',
        ]
    ])

        @includeWhen($stepsCount > 1, 'progressbar', ['steps' => $steps])

        @element([
            'classList' => [
                'c-card__body',
                'mod-frontend-form__content',
            ]
        ])
            @form([
                'validation' => false,
                'attributeList' => [
                    'data-js-frontend-form-id' => $moduleId,
                    'data-js-frontend-form-holding-post-id' => $holdingPostId,
                ],
                'classList' => [
                    'mod-frontend-form__form',
                ],
            ])
                @element([
                    'classList' => [
                        'u-position--relative',
                        'mod-frontend-form__steps',
                    ]
                ])
                    @foreach($steps as $index => $step)
                        @include('step', ['step' => $step, 'index' => $index])
                    @endforeach

                @endelement

                @include('partials.formStepButtons')
            @endform
            @include('partials.notices')
            @include('partials.disclaimer')
        @endelement
    @endelement
@endcard
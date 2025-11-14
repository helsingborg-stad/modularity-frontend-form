@paper([
    'classList' => [
        'mod-frontend-form',
        'o-layout-grid',
        'o-layout-grid--gap-12',
        'u-level-3',
        'u-padding__y--4',
    ],
    'attributeList' => [
        'data-js-frontend-form' => 'true'
    ]
])

    @includeWhen(empty($hideTitle) && !empty($postTitle), 'partials.module-title')

    @include('progressbar', ['steps' => $steps])

    @form([
        'validation' => false,
        'attributeList' => [
            'data-js-frontend-form-id' => $moduleId,
        ],
        'classList' => [
            'mod-frontend-form__form',
            'u-padding__x--4',
        ],
    ])
        @element([
            'classList' => [
                'u-position--relative',
                'mod-frontend-form__steps',
            ]
        ])
            @include('partials.working')

            @foreach($steps as $index => $step)
                @include('step', ['step' => $step, 'index' => $index])
            @endforeach

        @endelement

        @include('partials.formStepButtons')
    @endform
    @include('partials.notices')
@endpaper
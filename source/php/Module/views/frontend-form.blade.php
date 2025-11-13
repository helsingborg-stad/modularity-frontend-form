@paper([
    'classList' => [
        'mod-frontend-form',
        'o-layout-grid',
        'o-layout-grid--gap-12',
        'u-level-3'
    ],
    'attributeList' => [
        'data-js-frontend-form' => 'true'
    ],
    'padding' => 4
])

    @includeWhen(empty($hideTitle) && !empty($postTitle), 'partials.module-title')

    @include('progressbar', ['steps' => $steps])

    @form([
        'validation' => false,
        'attributeList' => [
            'data-js-frontend-form-id' => $moduleId,
        ],
        'classList' => [
            'mod-frontend-form__form'
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
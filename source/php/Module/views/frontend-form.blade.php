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
        'validation' => false,
        'attributeList' => [
            'data-js-frontend-form-id' => $moduleId,
        ],
        'classList' => [
            'mod-frontend-form__form'
        ],
    ])

        @element(['classList' => ['u-position--relative', 'mod-frontend-form__steps']])

            @include('partials.working')

            @foreach($steps as $index => $step)
                @include('step', ['step' => $step, 'index' => $index])
            @endforeach

        @endelement

        @include('partials.formStepButtons')
    @endform
    @include('partials.notices')
@endelement
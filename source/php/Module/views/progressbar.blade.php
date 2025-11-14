@element([
    'componentElement' => 'ul',
    'classList' => [
        'mod-frontend-form__progressbar',
        'u-padding__y--4'
    ]
])
    @foreach($steps as $index => $step)
        @element([
            'componentElement' => 'li',
            'classList' => [
                'mod-frontend-form__progressbar-step',
                'u-margin__top--0',
                'u-padding__left--0',
                $loop->first ? 'is-active' : '',
                $loop->first ? 'is-editable' : '',
                $loop->first ? 'is-first' : '',
                $loop->last ? 'is-last' : '',
            ],
            'attributeList' => [
                'data-js-frontend-form-progressbar-step' => $index
            ]
        ])
        @icon([
            'classList' => [
                'mod-frontend-form__progressbar-step-button',
            ],
            'icon' => 'remove',
            'size' => 'sm'
        ])
        @endicon
            @element([
                'componentElement' => 'span',
                'classList' => [
                    'mod-frontend-form__progressbar-step-indicator'
                ]
            ])
                <!-- progress indicator -->
            @endelement
            @element([
                'classList' => [
                    'u-margin__top--2',
                    'mod-frontend-form__progressbar-step-title',
                    'u-color__text--darker',
                    'u-bold',
                    'u-text-small'
                ]
            ])
                {{ $step['title'] }}
            @endelement
        @endelement
    @endforeach
@endelement
@element([
    'componentElement' => 'ul',
    'classList' => [
        'mod-frontend-form__progressbar'
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
            @element([
                'classList' => [
                    'mod-frontend-form__progressbar-step-button',
                ],
            ])
                @icon([
                    'icon' => 'remove',
                    'size' => 'sm',
                    'classList' => [
                        'mod-frontend-form__progressbar-step-icon'
                    ]
                ])
                @endicon
                <span style="color:black;position:absolute;padding-left:2.25rem;white-space:nowrap;height:2rem;display:flex;align-items:center;background:yellow;">
                {{ $step['title'] }}
                </span>
            @endelement
        @endelement
    @endforeach
@endelement
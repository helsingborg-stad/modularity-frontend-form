@element([
    'componentElement' => 'ul',
    'classList' => [
        'mod-frontend-form__progressbar',
    ]
])
    @foreach($steps as $index => $step)
        @element([
            'componentElement' => 'li',
            'classList' => [
                'mod-frontend-form__progressbar-step',
                'u-margin__top--0',
                $loop->first ? 'is-active' : '',
                $loop->first ? 'is-editable' : '',
                $loop->first ? 'is-first' : '',
                $loop->last  ? 'is-last' : '',
            ],
            'attributeList' => [
                'data-js-frontend-form-progressbar-step' => $index
            ]
        ])

            {{-- Step icon & button --}}
            @element([
                'componentElement' => 'button',
                'classList' => [
                    'mod-frontend-form__progressbar-step-button',
                ],
                'attributeList' => [
                    'type' => 'button',
                    'aria-label' => 'Go to step ' . ($index + 1) . ': ' . $step['title'],
                ]
            ])
                @icon([
                    'classList' => [
                        'mod-frontend-form__progressbar-step-button-icon',
                    ],
                    'icon' => $step['icon'] ?: 'counter_' . ($index + 1),
                    'size' => 'md'
                ])
                @endicon
            @endelement

            {{-- Step title & description --}}
            @element([
                'classList' => [
                    'mod-frontend-form__progressbar-step-content',
                ]
            ])
                @typography([
                    'element' => 'h4',
                    'variant' => 'h4',
                    'classList' => [
                        'mod-frontend-form__progressbar-step-title',
                        'u-color__text--darker',
                        'u-bold',
                        'u-text-small'
                    ]
                ])
                    {{ $step['title'] }}
                @endtypography

                @typography([
                    'element' => 'div',
                    'variant' => 'meta',
                    'classList' => [
                        'mod-frontend-form__progressbar-step-number',
                    ]
                ])
                    Step {{ $index + 1 }}/{{ count($steps) }}
                @endtypography
            @endelement

            {{-- Status icon: OK --}}
            @icon([
                'classList' => [
                    'mod-frontend-form__progressbar-step-status-icon',
                    'mod-frontend-form__progressbar-step-status-icon-ok',
                ],
                'icon' => 'check',
                'size' => 'md',
                'attributeList' => [
                    'aria-label' => 'Completed',
                    'aria-hidden' => 'true'
                ]
            ])
            @endicon

            {{-- Status icon: ERR --}}
            @icon([
                'classList' => [
                    'mod-frontend-form__progressbar-step-status-icon',
                    'mod-frontend-form__progressbar-step-status-icon-error',
                ],
                'icon' => 'error',
                'size' => 'md',
                'attributeList' => [
                    'aria-label' => 'Error',
                    'aria-hidden' => 'true'
                ]
            ])
            @endicon

        @endelement

        {{-- Divider --}}
        @if(!$loop->last)
            @element([
                'componentElement' => 'li',
                'classList' => [
                    'mod-frontend-form__progressbar-divider',
                ],
                'attributeList' => [
                    'aria-hidden' => 'true'
                ]
            ])
                @icon([
                    'icon' => 'chevron_right',
                    'size' => 'md',
                    'classList' => [
                        'mod-frontend-form__progressbar-divider-icon',
                    ]
                ])
                @endicon
            @endelement
        @endif

    @endforeach
@endelement
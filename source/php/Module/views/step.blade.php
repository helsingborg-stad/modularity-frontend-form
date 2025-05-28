@if (!empty($step['fields']))
    @paper([
        'padding' => 4,
        'attributeList' => [
            'data-js-frontend-form-step-container' => $index,
            'style' => 'z-index: ' . (1000 - $index) . ';',
        ],
        'classList' => [
            'mod-frontend-form__step-container',
            $index === 0 ? 'is-editable' : '',
            $index === 0 ? 'is-active' : '',
            $index === 0 ? 'is-loading' : '',
        ]
    ])
        @element([
            'classList' => [
                'mod-frontend-form__step-header',
            ]
        ])

            <!-- Step Header -->
            @element([])
                @if($step['title'])
                    @typography([
                        'element' => 'h2',
                        'classList' => [
                            'mod-frontend-form__step-header-title'
                        ]
                    ])
                        {{ $step['title'] }} @icon([
                            'icon' => 'error',
                            'filled' => false,
                            'size' => 'md',
                            'classList' => [
                                'mod-frontend-form__step-header-error-icon'
                            ]
                        ])
                        @endicon
                    @endtypography
                @endif
                @if($step['description'])
                    @typography([
                        'element' => 'div',
                        'classList' => [
                            'u-margin__top--1',
                            'mod-frontend-form__step-header-description'
                        ]
                    ])
                        {!! $step['description'] !!}
                    @endtypography
                @endif
            @endelement

            <!-- Step Header Edit Button -->
            @button([
                'icon' => 'edit',
                'text' => $lang->edit,
                'size' => 'sm',
                'style' => 'basic',
                'reversePositions' => true,
                'classList' => [
                    'mod-frontend-form__step-header-edit',
                    $index === 0 ? 'u-visibility--hidden' : ''
                ],
                'attributeList' => [
                    'role' => 'button',
                    'data-js-frontend-form-step-edit' => $index,
                ]
            ])
            @endbutton

        @endelement
        @if ($index === 0)
            @loader([
                'shape' => 'linear',
                'size' => 'xs',
                'attributeList' => [
                    'style' => 'top: -2rem;'
                ],
                'classList' => [
                    'u-margin__top--2',
                    'u-margin__bottom--2',
                    'mod-frontend-form__step-loader',
                ]
            ])
            @endloader
        @endif
        @element([
            'attributeList' => [
                'data-js-frontend-form-step' => $index,
            ],
            'classList' => [
                'mod-frontend-form__step',
                $index === 0 ? 'is-visible' : ''
            ]
        ])
            @foreach($step['fields'] as $field)
                @if (isset($field['view']))  
                    @includeIf('fields.' . $field['view'], ['field' => $field])
                @endif
            @endforeach
        @endelement
    @endpaper
@endif

@if (!empty($step['fields']))
    @paper([
        'padding' => 4,
        'attributeList' => [
            'data-js-frontend-form-step-container' => $index,
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
                    ])
                        {{ $step['title'] }}
                    @endtypography
                @endif
                @if($step['description'])
                    @typography([
                        'element' => 'div',
                        'classList' => ['u-margin__top--1']
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

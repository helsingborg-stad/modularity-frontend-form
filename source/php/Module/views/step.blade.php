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
            @element([])
                @if($step['title'])
                    @typography([
                        'element' => 'h2',
                    ])
                        {{ $step['title'] }}
                    @endtypography
                @endif
                @if($step['title'])
                    @typography([
                        'classList' => ['u-margin__top--1']
                    ])
                        Beskrivning av steg
                    @endtypography
                @endif
            @endelement
            @button([
                'icon' => 'edit',
                'size' => 'md',
                'style' => 'basic',
                'classList' => [
                    'mod-frontend-form__step-header-edit',
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
                    'style' => $index === 0 ? '' : 'display: none;',
                ],
                'classList' => [
                    'mod-frontend-form__step',
                    $index === 0 ? 'is-visible' : '',
                    'u-margin__top--2'
                ]
            ])
                @foreach($step['fields'] as $field)
                    @includeIf('fields.' . $field['view'], ['field' => $field])
                @endforeach
            @endelement
    @endpaper
@endif

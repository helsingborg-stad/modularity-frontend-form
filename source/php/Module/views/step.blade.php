@if (!empty($step['fields']))
    @element([
        'attributeList' => [
            'data-js-frontend-form-step-container' => $index,
        ],
        'classList' => [
            'mod-frontend-form__step-container',
            'o-layout-grid',
            'o-layout-grid--gap-6',
            $index === 0 ? 'is-editable' : '',
            $index === 0 ? 'is-active' : '',
            $index === 0 ? 'is-loading' : '',
        ],
    ])
        @element([
            'classList' => [
                'mod-frontend-form__step-header',
            ]
        ])

            <!-- Step Header -->
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
                    {!! wp_kses_post($step['description']) !!}
                @endtypography
            @endif
        @endelement
        @element([
            'attributeList' => [
                'data-js-frontend-form-step' => $index,
            ],
            'classList' => [
                'mod-frontend-form__step',
                'o-layout-grid',
                'o-layout-grid--cols-12',
                'o-layout-grid--row-gap-8',
                'o-layout-grid--column-gap-6',
            ]
        ])
            <!-- Step Fields -->
            @foreach($step['fields'] as $field)
                @if (isset($field['view']))  
                    @includeIf('fields.' . $field['view'], ['field' => $field])
                @endif
            @endforeach
        @endelement
    @endelement
@endif

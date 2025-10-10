@if (!empty($field['fields']))
    @element([
        'attributeList' => $field['attributeList'],
        'classList' => $field['classList'],
        'id' => $field['id'],
    ])
        @include('partials.repeaterHeader')
        @element([
            'classList' => [
                'mod-frontend-form__repeater-container'
            ],
            'attributeList' => [
                'data-js-repeater-row-container' => 'true'
            ]
        ])

        {{-- Repeater Row Template --}}
            @element([
                'componentElement' => 'template',
                'attributeList' => [
                    'data-js-repeater-row-template' => 'true',
                ]
            ])
                @include('partials.repeaterRow')
            @endelement
        @endelement
        @button([
            'text' => $field['buttonLabel'],
            'icon' => 'playlist_add',
            'color' => 'secondary',
            'style' => 'filled',
            'attributeList' => [
                'data-js-repeater-add-row' => 'true'
            ],
            'classList' => [
                'mod-frontend-form__repeater-add',
                'u-margin__top--2'
            ]
        ])
        @endbutton
    @endelement
@endif
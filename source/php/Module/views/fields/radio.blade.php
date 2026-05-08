@element([
    'attributeList' => $field['attributeList'],
    'classList' => $field['classList'],
    'id'        => $field['id']
])
    @if (!empty($field['label']))
        @typography([
            'element' => 'span',
            'classList' => [
                'mod-frontend-form__custom-label'
            ],
        ])
            {{ $field['label'] }} {!! !empty($field['attributeList']['data-js-required']) ? '<span class="u-color__text--danger">*</span>' : '' !!}
        @endtypography
    @endif
    @element([
        'componentElement' => 'div',
        'classList' => ['mod-frontend-form__field--radio__options'],
    ])
        @foreach ($field['choices'] as $choice)
            @option($choice)
            @endoption
        @endforeach
    @endelement
@endelement
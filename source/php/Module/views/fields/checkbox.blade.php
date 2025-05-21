@element([
    'componentElement' => 'fieldset',
    'attributeList' => $field['attributeList'] ?? [],
])
    @if (!empty($field['label']))
        @typography([
            'element' => 'legend',
        ])
            {{ $field['label'] }} {!! $field['required'] ? '<span class="u-color__text--danger">*</span>' : '' !!}
        @endtypography
    @endif
    @foreach ($field['choices'] as $choice)
        @option($choice)
        @endoption
    @endforeach
@endelement
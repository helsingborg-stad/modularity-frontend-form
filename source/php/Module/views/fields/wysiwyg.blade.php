@element([
    'attributeList' => $field['attributeList'],
    'id' => $field['id'],
    'classList' => $field['classList']
])
   @if (!empty($field['label']))
        @typography([
            'element' => 'span',
            'classList' => [
                'mod-frontend-form__custom-label'
            ],
        ])
            {{ $field['label'] }} {!! !empty($field['required']) ? '<span class="u-color__text--danger">*</span>' : '' !!}
        @endtypography
    @endif
    @field([
        'type' => 'hidden',
        'attributeList' => [
            'data-js-wysiwyg-hidden-field' => true
        ],
        'required' => $field['required'] ?? false,
        'name' => $field['name'],
        'disabled' => true
    ])
    @endfield
<!-- Wysiwyg -->
@endelement
@element([
    'attributeList' => $field['attributeList'],
    'classList' => [
        'mod-frontend-form__wysiwyg',
    ],
])
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
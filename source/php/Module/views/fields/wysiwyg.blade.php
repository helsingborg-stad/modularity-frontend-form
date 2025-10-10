@element([
    'attributeList' => $field['attributeList'],
    'id' => $field['id'],
    'classList' => $field['classList']
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
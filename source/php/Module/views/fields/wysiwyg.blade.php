
@dump($field)
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
            {{ $field['label'] }} {!! !empty($field['attributeList']['data-js-required']) ? '<span class="u-color__text--danger">*</span>' : '' !!}
        @endtypography
    @endif
    @field([
        'type' => 'hidden',
        'attributeList' => [
            'data-js-wysiwyg-hidden-field' => true
        ],
        'name' => $field['name'],
        'disabled' => true
    ])
    @endfield
<!-- Wysiwyg -->
@endelement
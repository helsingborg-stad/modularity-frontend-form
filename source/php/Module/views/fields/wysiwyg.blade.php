@element([
    'attributeList' => $field['attributeList'],
    'id' => $field['id'],
    'classList' => $field['classList']
])
   @if (!empty($field['label']))
        @typography([
            'element' => 'span',
            'classList' => [
                'c-field__label'
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
    @foreach ([
        'format_bold',
        'format_italic',
        'format_underlined',
        'format_strikethrough',
        'format_h1',
        'format_h2',
        'format_quote',
        'format_list_numbered',
        'format_list_bulleted',
        'link'
    ] as $icon)
        <template data-js-wysiwyg-icon-template="{{ $icon }}">
            @icon([
                'icon' => $icon,
                'size' => 'md'
            ])
            @endicon
        </template>
    @endforeach
<!-- Wysiwyg -->
@endelement

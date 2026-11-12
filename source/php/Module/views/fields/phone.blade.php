@element([
    'attributeList' => $field['attributeList'],
    'classList' => $field['classList'],
])
    @field(array_merge($field, ['description' => '', 'attributeList' => [], 'classList' => []]))
    @endfield
    <p id="{{ $field['phoneHelpId'] }}">{{ $field['description'] }}</p>
@endelement

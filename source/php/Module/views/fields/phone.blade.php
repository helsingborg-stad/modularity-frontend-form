@element([
    'attributeList' => $field['attributeList'],
    'classList' => $field['classList'],
])
    @field(array_merge(
        array_diff_key($field, array_flip(['view', 'disabled', 'rows', 'phoneHelpId'])),
        ['description' => '', 'attributeList' => [], 'classList' => []]
    ))
    @endfield
    <p id="{{ $field['phoneHelpId'] }}">{{ $field['description'] }}</p>
@endelement

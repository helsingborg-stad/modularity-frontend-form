@element([
    'attributeList' => $field['attributeList'],
    'classList' => $field['classList'],
    'id'        => $field['id']
])
    @foreach ($field['choices'] as $choice)
        @option($choice)
        @endoption
    @endforeach
@endelement
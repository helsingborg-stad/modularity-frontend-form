@element([
    'attributeList' => $field['attributeList'],
    'classList' => $field['classList'],
])
    @foreach ($field['choices'] as $choice)
        @option($choice)
        @endoption
    @endforeach
@endelement
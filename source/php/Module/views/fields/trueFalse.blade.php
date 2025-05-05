@element([
    'attributeList' => $field['attributeList'] ?? [],
])
    @foreach ($field['choices'] as $choice)
        @option($choice)
        @endoption
    @endforeach
@endelement
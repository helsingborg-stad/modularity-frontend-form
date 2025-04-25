{{-- TODO: How should we use checkbox? --}}
@element([
    'attributeList' => $field['attributeList'] ?? [],
])
    @foreach ($field['choices'] as $choice)
        @option($choice)
        @endoption
    @endforeach
@endelement
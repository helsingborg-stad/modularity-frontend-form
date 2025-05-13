{{-- TODO: How should we use checkbox? --}}
@element([
    'attributeList' => $field['attributeList'] ?? [],
])
    @notice([
        'attributeList' => [
            'data-js-field-notice' => 'true',
        ],
        'icon' => [
            'icon' => 'restaurant'
        ]
    ])
    @endnotice
    @foreach ($field['choices'] as $choice)
        @option($choice)
        @endoption
    @endforeach
@endelement
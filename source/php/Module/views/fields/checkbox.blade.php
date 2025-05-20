@element([
    'attributeList' => $field['attributeList'] ?? [],
])
    @if (!empty($field['label']))
        @typography([
            'element' => 'h2',
            'variant' => 'h4',
            'classList' => [
                'u-margin__bottom--1',
                'u-margin__top--0'
            ],
        ])
            {{ $field['label'] }}
        @endtypography
    @endif
    @foreach ($field['choices'] as $choice)
        @option($choice)
        @endoption
    @endforeach
@endelement
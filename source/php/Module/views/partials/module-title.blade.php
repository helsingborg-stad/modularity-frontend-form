@element([
    'classList' => [
        'u-text-align--center'
    ]
])
    @if (empty($hideTitle) && !empty($postTitle))
        @typography([
            'element' => $element ?? 'h2',
            'variant' => $variant ?? 'h2',
            'classList' => $classList ?? ['module-title']
        ])
            {!! $postTitle !!}
        @endtypography
    @endif
@endelement
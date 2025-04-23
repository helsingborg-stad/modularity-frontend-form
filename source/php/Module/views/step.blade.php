@if (!empty($step['fields']))
    @element([
        'attributeList' => [
            'data-js-frontend-form-step' => $index
        ],
        'classList' => [
            $index === 0 ? '' : 'u-display--none',
        ]
    ])
        @if($step['title'])
            @typography([
                'element' => 'h2',
            ])
                {{ $step['title'] }}
            @endtypography
        @endif
        @foreach($step['fields'] as $field)
            @includeIf('fields.' . $field['view'], ['field' => $field])
        @endforeach
    @endelement
@endif
@paper(['padding' => 4])
    @if (!empty($step['fields']))
        @element([
            'attributeList' => [
                'data-js-frontend-form-step' => $index,
                'style' => $index === 0 ? '' : 'display: none;',
            ],
            'classList' => [
                'mod-frontend-form__step',
                $index === 0 ? 'is-visible' : '',
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
@endpaper

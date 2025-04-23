@paper(['padding' => 4])
    @if (!empty($step['fields']))
        @element([
            'attributeList' => [
                'data-js-frontend-form-step' => $index
            ],
            'classList' => [
                'mod-frontend-form__step',
                $index === 0 ? 'u-display--block' : '',
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
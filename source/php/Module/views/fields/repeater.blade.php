@dump($field)
@if (!empty($field['fields']))
@typography([
    'element' => 'h2',
    'variant' => 'h4',
])
    {{ $field['label'] }}
@endtypography
    @element([
        'attributeList' => [
            'data-js-form-repeater' => 'true'
        ]
    ])
        @element([
            'componentElement' => 'template'
        ])
            @foreach ($field['fields'] as $subField)
                @includeIf('fields.' . $subField['view'], ['field' => $subField])
            @endforeach
        @endelement
    @endelement

    @button([
        'text' => 'New row [+]',
        'color' => 'primary',
        'style' => 'filled',
    ])
    @endbutton
@endif
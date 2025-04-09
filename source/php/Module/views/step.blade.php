@if (!empty($step['fields']))
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
@endif
@element([
    'classList' => [
        'mod-frontend-form'
    ],
    'attributeList' => [
        'data-js-frontend-form' => 'true'
    ]
])
    @form([])
        @foreach($steps as $step)
        @include('step', ['step' => $step])
        @endforeach
    @endform
@endelement
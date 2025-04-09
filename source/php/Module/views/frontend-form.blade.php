@element([
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
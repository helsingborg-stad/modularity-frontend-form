@element(['classList' => ['mod-frontend-form-working', 'u-rounded--16', 'u-display--none'], 'attributeList' => ['data-js-frontend-form-working' => 'true']])

  @icon([
    'icon' => 'send',
    'size' => 'xxl',
    'classList' => [
        'mod-frontend-form-working__icon'
    ],
    'attributeList' => [
        'data-js-frontend-form-working__icon' => 'true'
    ]
  ])
  @endicon

  @progressBar([
    'classList' => [
        'mod-frontend-form-working__progress-bar'
    ],
    'attributeList' => [
        'data-js-frontend-form-working__progress' => 'true'
    ],
    'value' => 25,
  ])
  @endprogressBar

  @element(['classList' => ['u-display--flex', 'u-flex--column', 'u-align-items--center', 'u-justify-content--center', 'u-flex-direction--column', 'u-gap--1']])
    @typography([
        'element' => 'div',
        'variant' => 'body',
        'classList' => [
            'mod-frontend-form-working__title'
        ],
        'attributeList' => [
            'data-js-frontend-form-working__title' => 'true'
        ],
    ])
      @if($lang->submitting)
          {{ $lang->submitting }}
      @endif
    @endtypography
    
    @typography([
        'element' => 'div',
        'variant' => 'meta',
        'classList' => [
            'mod-frontend-form-working__description'
        ],
        'attributeList' => [
            'data-js-frontend-form-working__description' => 'true'
        ],
    ])
      @if($lang->working)
          {{ $lang->submitInit }}
      @endif
    @endtypography
  @endelement

@endelement
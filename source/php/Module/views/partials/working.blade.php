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
  
  @typography([
      'element' => 'span',
      'variant' => 'meta',
      'classList' => [
          'mod-frontend-form-working__description'
      ],
      'attributeList' => [
          'data-js-frontend-form-working__description' => 'true'
      ],
  ])
    Here we wörking on your request. Please wait a moment.
  @endtypography

@endelement
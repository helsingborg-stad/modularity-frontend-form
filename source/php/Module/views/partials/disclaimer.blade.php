@typography([
    'variant' => 'meta',
    'classList' => [
        'mod-frontend-form__disclaimer',
        'u-margin__top--4',
    ],
    'attributeList' => [
        'style' => 'max-width: unset;'
    ]
])
  {!! wp_kses_post($disclaimerText) !!}
@endtypography
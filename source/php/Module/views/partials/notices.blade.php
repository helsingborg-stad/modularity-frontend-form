@element([
    'componentElement' => 'template',
    'attributeList' => [
        'data-js-frontend-form-error-notice' => 'true',
    ]
])
    @element([
        'classList' => [
            'mod-frontend-form__notice'
        ],
        'attributeList' => [
            'data-js-frontend-form-notice-message' => true,
        ]
    ])
        <!-- error notice -->
    @endelement
@endelement

@element([
    'componentElement' => 'template',
    'attributeList' => [
        'data-js-frontend-form-success-notice' => 'true',
    ]
])
    @element([
        'classList' => [
            'mod-frontend-form__notice'
        ],
        'attributeList' => [
            'data-js-frontend-form-notice-message' => true,
        ]
    ])
        <!-- success notice -->
    @endelement
@endelement
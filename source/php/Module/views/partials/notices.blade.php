@element([
    'componentElement' => 'template',
    'attributeList' => [
        'data-js-frontend-form-error-notice' => 'true',
    ]
])
    @notice([
        'type' => 'danger',
        'icon' => ['icon' => 'error'],
    ])
        @element([
            'componentElement' => 'span',
            'classList' => [
                'c-notice__message'
            ],
            'attributeList' => [
                'data-js-frontend-form-notice-message' => true,
            ]
        ])
        html
        @endelement
    @endnotice
@endelement

@element([
    'componentElement' => 'template',
    'attributeList' => [
        'data-js-frontend-form-success-notice' => 'true',
    ]
])
    @notice([
        'type' => 'success',
        'icon' => ['icon' => 'check_circle'],
    ])
        @element([
            'componentElement' => 'span',
            'classList' => [
                'c-notice__message'
            ],
            'attributeList' => [
                'data-js-frontend-form-notice-message' => true,
            ]
        ])
        html
        @endelement
    @endnotice
@endelement
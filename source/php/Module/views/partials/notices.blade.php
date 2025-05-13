@element([
    'componentElement' => 'template'
])
    @notice([
        'type' => 'danger',
        'icon' => ['icon' => 'error'],
        'attributeList' => [
            'data-js-frontend-form-error-notice' => 'true',
        ],
        'message' => [
            'text' => '{TEXT_PLACEHOLDER}'
        ]
    ])
    @endnotice
    @notice([
        'type' => 'success',
        'icon' => ['icon' => 'check_circle'],
        'attributeList' => [
            'data-js-frontend-form-error-notice' => 'true',
        ],
        'message' => [
            'text' => '{TEXT_PLACEHOLDER}'
        ]
    ])
    @endnotice
@endelement
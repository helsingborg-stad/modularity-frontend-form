@element([
    'componentElement' => 'template',
    'attributeList' => [
        'data-js-frontend-form-error-notice' => 'true',
    ]
])
    @notice([
        'type' => 'danger',
        'icon' => ['icon' => 'error'],
        'message' => [
            'text' => '{MESSAGE_PLACEHOLDER}'
        ]
    ])
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
        'message' => [
            'text' => '{MESSAGE_PLACEHOLDER}'
        ]
    ])
    @endnotice
@endelement
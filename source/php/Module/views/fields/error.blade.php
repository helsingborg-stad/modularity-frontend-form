@notice([
    'type' => 'danger',
    'message' => [
        'text' => $lang->followingFieldIsNotSupported . ': ' . $field['unsupportedFieldType'] . ' (' . $field['label'] . ')'
    ],
    'icon' => [
        'size' => 'md',
        'icon' => 'report'
    ]
])
@endnotice
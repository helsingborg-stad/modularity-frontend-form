@notice([
    'type' => 'danger',
    'classList' => ['o-layout-grid--col-span-12'],
    'message' => [
        'text' => $lang->followingFieldIsNotSupported . ': ' . $field['unsupportedFieldType'] . ' (' . $field['label'] . ')'
    ],
    'icon' => [
        'size' => 'md',
        'icon' => 'report'
    ]
])
@endnotice
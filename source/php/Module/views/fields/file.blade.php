@fileinput([
    'label'    => $field['label'],
    'name'     => $field['name'] . '[]',
    'required' => $field['required'],
    'accept'   => $field['accept'],
    'id'       => $field['id'],
    'attributeList' => $field['attributeList'],
    'description' => $field['description'],
    'maxSize' => $field['maxSize'],
    'classList' => $field['classList'],
    'uploadErrorMessage' => $lang->followingFilesCouldNotBeUploaded . ': ',
    'accept' => $field['accept']
])
@endfileinput
@imageinput([
    'label'    => $field['label'],
    'name'     => $field['name'] . '[]',
    'id'       => $field['id'],
    'required' => $field['required'],
    'accept'   => $field['accept'],
    'attributeList' => $field['attributeList'],
    'description' => $field['description'],
    'filesMax' => $field['filesMax'],
    'filesMin' => $field['filesMin'],
    'maxSize' => $field['maxSize'],
    'classList' => $field['classList'],
    'uploadErrorMessage' => $lang->followingFilesCouldNotBeUploaded . ': ',
    'uploadErrorMessageMinFiles' => $field['errorGalleryMinFiles']
])
@endimageinput
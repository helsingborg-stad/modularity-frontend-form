<?php

namespace ModularityFrontendForm\FieldMapping\Mapper\Acf;

use ModularityFrontendForm\FieldMapping\Mapper\Interfaces\FieldMapperInterface;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperConstruct;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperGetInstance;

class FileFieldMapper implements FieldMapperInterface
{
    use FieldMapperConstruct;
    use FieldMapperGetInstance;

    public function map(): array
    {
        $mapped = (new BasicFieldMapper($this->field, $this->lang, 'file'))->map();

        $mapped['accept'] = !empty($this->field['mime_types'])
            ? str_replace(' ', ',', $this->field['mime_types'])
            : 'audio/*,video/*,image/*';

        $mapped['uploadErrorMessage'] = $this->lang->followingFilesCouldNotBeUploaded . ': ';

        return $mapped;
    }
}
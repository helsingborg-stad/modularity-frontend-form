<?php

namespace ModularityFrontendForm\Module;

use EventManager\Modules\Module;

class Register extends Module
{
    public function getModuleName(): string
    {
        return 'FrontendForm';
    }

    public function getModulePath(): string
    {
        if (!defined('MODULARITYFRONTENDFORM_PATH')) {
            return '';
        }

        return constant('MODULARITYFRONTENDFORM_PATH') . 'source/php/Modules/FrontendForm/';
    }
}

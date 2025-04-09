<?php

namespace ModularityFrontendAcfForm\Module;

class FrontendAcfForm extends \Modularity\Module
{
    public $slug = 'frontend-acf-form';
    public $supports = array();
    public $blockSupports = array(
        'align' => ['full'],
        'mode' => false
    );



    public function init()
    {
        //Define module
        $this->nameSingular = __("FrontendAcfForm", 'modularity-frontend-acf-form');
        $this->namePlural = __("FrontendAcfForms", 'modularity-frontend-acf-form');
        $this->description = __("Outputs a map.", 'modularity-frontend-acf-form');
    }

     /**
     * View data
     * @return array
     */
    public function data(): array
    {
        $data = [];

        return $data;
    }

    public function template(): string
    {
        return "frontend-acf-form.blade.php";
    }

    /**
     * Available "magic" methods for modules:
     * init()            What to do on initialization
     * data()            Use to send data to view (return array)
     * style()           Enqueue style only when module is used on page
     * script            Enqueue script only when module is used on page
     * adminEnqueue()    Enqueue scripts for the module edit/add page in admin
     * template()        Return the view template (blade) the module should use when displayed
     */
}

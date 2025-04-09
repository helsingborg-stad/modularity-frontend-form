<?php


namespace ModularityForm;

/**
 * Class App
 * @package ModularityForm
 */
class App
{
    public function __construct()
    {
        add_action('plugins_loaded', array($this, 'registerModule'));
    }

    /**
     * Register the module
     * @return void
     */
    public function registerModule()
    {
        if (function_exists('modularity_register_module')) {
            modularity_register_module(
                MODULARITYFORM_PATH . 'source/php/Module/',
                'Form'
            );
        }
    }
}

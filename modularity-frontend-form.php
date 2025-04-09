<?php

/**
 * Plugin Name:       Modularity Frontend Form
 * Plugin URI:        https://github.com/helsingborg-stad/modularity-frontend-form
 * Description:       A plugin to create a modularity module for a form.
 * Version: 0.0.0
 * Author:            Niclas Norin
 * Author URI:        https://github.com/helsingborg-stad
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       modularity-frontend-form
 * Domain Path:       /languages
 */

 // Protect agains direct file access
if (! defined('WPINC')) {
    die;
}

define('MODULARITYFRONTENDFORM_PATH', plugin_dir_path(__FILE__));
define('MODULARITYFRONTENDFORM_URL', plugins_url('', __FILE__));
define('MODULARITYFRONTENDFORM_MODULE_VIEW_PATH', MODULARITYFRONTENDFORM_PATH . 'source/php/Module/views');

// Endpoint address

require_once MODULARITYFRONTENDFORM_PATH . 'Public.php';

// Register the autoloader
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
}

add_action('init', function () {
    load_plugin_textdomain('modularity-frontend-form', false, plugin_basename(dirname(__FILE__)) . '/languages');
});

add_filter('/Modularity/externalViewPath', function ($arr) {
    $arr['mod-frontend-form'] = MODULARITYFRONTENDFORM_MODULE_VIEW_PATH;
    return $arr;
}, 10, 3);

// Acf auto import and export
add_action('acf/init', function () {
    $acfExportManager = new \AcfExportManager\AcfExportManager();
    $acfExportManager->setTextdomain('modularity-frontend-form');
    $acfExportManager->setExportFolder(MODULARITYFRONTENDFORM_PATH . 'source/php/AcfFields/');
    $acfExportManager->autoExport(array(

    ));
    $acfExportManager->import();
});

// Start application
new ModularityFrontendForm\App();
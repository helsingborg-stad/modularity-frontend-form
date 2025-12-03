<?php

/**
 * Plugin Name:       Modularity Frontend Form
 * Plugin URI:        https://github.com/helsingborg-stad/modularity-frontend-form
 * Description:       A plugin to create a modularity module for a form.
 * Version: 0.61.2
 * Author:            Niclas Norin, Sebastian Thulin
 * Author URI:        https://github.com/helsingborg-stad
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       modularity-frontend-form
 * Domain Path:       /languages
 */

use AcfService\Implementations\NativeAcfService;
use WpService\Implementations\NativeWpService;

 // Protect agains direct file access
if (! defined('WPINC')) {
    die;
}

define('MODULARITYFRONTENDFORM_PATH', plugin_dir_path(__FILE__));
define('MODULARITYFRONTENDFORM_URL', plugins_url('', __FILE__));
define('MODULARITYFRONTENDFORM_MODULE_VIEW_PATH', MODULARITYFRONTENDFORM_PATH . 'source/php/Module/views');

$wpService = new NativeWpService();
$acfService = new NativeAcfService();

// Register the autoloader
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
}

$wpService->addAction('init', function () use ($wpService) {
    $wpService->loadPluginTextdomain('modularity-frontend-form', false, $wpService->pluginBasename(dirname(__FILE__)) . '/languages');
});

$wpService->addFilter('/Modularity/externalViewPath', function ($arr) {
    $arr['mod-frontend-form'] = MODULARITYFRONTENDFORM_MODULE_VIEW_PATH;
    return $arr;
}, 10, 3);

// Acf auto import and export
$wpService->addAction('acf/init', function () {
    $acfExportManager = new \AcfExportManager\AcfExportManager();
    $acfExportManager->setTextdomain('modularity-frontend-form');
    $acfExportManager->setExportFolder(MODULARITYFRONTENDFORM_PATH . 'source/php/AcfFields/');
    $acfExportManager->autoExport(array(
        'mod-frontend-forms' => 'group_6627a5e16d74c'
    ));

    $acfExportManager->import();
});

//Config 
$config                 = ModularityFrontendForm\Config\ConfigFactory::create($wpService); 
$moduleConfigFactory    = new ModularityFrontendForm\Config\ModuleConfigFactory($wpService, $acfService, $config);

// Start application
$app = new ModularityFrontendForm\App(
    $wpService, 
    $acfService,
    $config,
    $moduleConfigFactory
);
$app->addHooks();
<?php

/**
 * Plugin Name:       Modularity Frontend Form
 * Plugin URI:        https://github.com/helsingborg-stad/modularity-frontend-form
 * Description:       A plugin to create a modularity module for a form.
 * Version: 0.83.4
 * Author:            Niclas Norin, Sebastian Thulin
 * Author URI:        https://github.com/helsingborg-stad
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       modularity-frontend-form
 * Domain Path:       /languages
 */

use AcfService\Implementations\NativeAcfService;
use ModularityFrontendForm\DataProcessor\Handlers\HandlerFactory;
use ModularityFrontendForm\DataProcessor\Validators\ValidatorFactory;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;
use PsrLogger\Client\PhpErrorLogger;
use PsrLogger\LoggerFactory;
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

//Logger
$logLevel = defined('MODULARITY_FRONTEND_FORM_LOG_LEVEL') ? MODULARITY_FRONTEND_FORM_LOG_LEVEL : null;
$globalLogLevel = defined('APP_LOG_LEVEL') ? APP_LOG_LEVEL : LogLevel::ERROR;
$loggerFactory = new LoggerFactory('ModularityFrontendForm', [
    [
        'logger' => defined('WP_DEBUG_LOG') && WP_DEBUG_LOG ? new PhpErrorLogger() : new NullLogger(), 
        'logLevel' => $logLevel ?? $globalLogLevel
    ]
]);

//Factories
$validatorFactory   = new ValidatorFactory($wpService, $acfService, $config, $moduleConfigFactory);
$handlerFactory     = new HandlerFactory($wpService, $acfService, $config, $moduleConfigFactory, $loggerFactory->createLogger(['namespace' => 'DataProcessor']));

// Start application
$app = new ModularityFrontendForm\App(
    $wpService, 
    $acfService,
    $config,
    $moduleConfigFactory,
    $loggerFactory->createLogger(),
    $validatorFactory,
    $handlerFactory 
);
$app->addHooks();
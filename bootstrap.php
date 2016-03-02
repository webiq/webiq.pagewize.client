<?php
use PagewizeClient\PagewizeClient;
/**
 * Bootstrap file
 *
 * Will create instances of the PagewizeClient as Smarty
 *
 * @license https://github.com/webiq/webiq.pagewize.client/blob/master/LICENSE.md
 */
// check if Composer is installed..
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    die('Composer is not installed. Please read the section setup in the README.md');
}

// include autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Check if the config file exists
if (!file_exists(__DIR__ . '/config.php')) {
    die('Please create a config.php file. Please check config.sample.php');
}

/**
 * Import config file
 *
 * @var array $api
 * @var array $smartyConfig
 * @var array $environment
 */
require_once __DIR__ . '/config.php';

// set environment settings
error_reporting($environment['error_reporting']);

// create Pagewize client instance
$client = new PagewizeClient($api['key'], $api['debug'], $api['url'], $api['protocol']);

// create Smarty instance
$smarty = new Smarty();
$smarty->setTemplateDir($smartyConfig['template_dir']);
$smarty->setCacheDir($smartyConfig['cache_dir']);
$smarty->setCompileDir($smartyConfig['compile_dir']);
$smarty->setCaching($smartyConfig['cache']);

// should we remove all cache?
if (!$smartyConfig['cache']) {
    $smarty->clearAllCache();
}
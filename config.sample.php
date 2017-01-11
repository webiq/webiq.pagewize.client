<?php
/**
 * Config sample file. Can be used to create a config file
 *
 * @license https://github.com/webiq/webiq.pagewize.client/blob/master/LICENSE.md
 */
// API configuration
$api = [
    'key' => 'INSERT_YOUR_KEY_HERE',
    'debug' => false,
    'url' => 'api.pagewize.com',
    'protocol' => 'https'
];

// smarty configuration
$smartyConfig = [
    'template_dir' => __DIR__ . '/tpl',
    'compile_dir' => __DIR__ . '/tmp/compile',
    'cache_dir' => __DIR__ . '/tmp/cache',
    'cache' => false
];

// environment settings
$environment = [
    'error_reporting' => E_ALL & ~E_NOTICE,
];

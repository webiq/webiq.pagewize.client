<?php
// API configuration
$api = [
    'key' => 'API_KEY123',
    'debug' => false,
    'url' => 'api.staging.pagewize.com',
    'protocol' => 'http'
];

// smarty configuration
$smartyConfig = [
    'template_dir' => __DIR__ . '/tpl',
    'compile_dir' => __DIR__ . '/compile',
    'cache_dir' => __DIR__ . '/cache',
    'cache' => false
];

// environment settings
$environment = [
    'error_reporting' => E_ALL,
];
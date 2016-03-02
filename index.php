<?php
/**
 * Index file, routes all traffic
 *
 * @license https://github.com/webiq/webiq.pagewize.client/blob/master/LICENSE.md
 */
use PagewizeClient\PagewizeClient;

/**
 * Use the bootstrap to start the application and create instances
 * of Smarty & the Pagewize Client
 *
 * @var Smarty         $smarty
 * @var PagewizeClient $client
 */
require_once __DIR__ . '/bootstrap.php';

// get the request from the url
$slug = $_SERVER['REQUEST_URI'];

// get the content that belongs to this url
$variables = $client->fetchContent($slug);

echo '<pre>';
print_r($variables);
echo '</pre>';
die();

// set the values
foreach ($variables as $variableName => $variableValue) {
    $smarty->assign($variableName, $variableValue);
}

/**
 * Show the right template based on the requested content
 */
switch ($variables['type']) {
    case 'post':
        $smarty->display('post.tpl');
        break;

    case 'page':
        $smarty->display('page.tpl');
        break;
}
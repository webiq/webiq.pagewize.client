<?php
/**
 * Index file, routes all traffic
 *
 * @license LICENSE.md
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
$response = $client->fetchContent($slug);

// result is true when the response code is 200, otherwise false
$result = $response['result'];

// http status code that came with the response
$statusCode = $response['code'];

// variables are to be found in the messages object
$variables = $response['message'];

// get requests are passed on and ONLY used for Smarty
if ($_SERVER['REQUEST_METHOD'] == 'GET' && $result) {
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

        case 'post_category':
            $smarty->display('post_category.tpl');
            break;
    }
}

// if result is false it can be a content-not-found error
if ($_SERVER['REQUEST_METHOD'] == 'GET' && $statusCode >= 400 && $statusCode < 500) {
    // set the values
    foreach ($variables as $variableName => $variableValue) {
        $smarty->assign($variableName, $variableValue);
    }

    $smarty->display('404.tpl');
}

// When submitting a new comment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $variables['type'] == 'post') {
    // init the parent comment id variable, otherwise extract from the $_POST payload
    $parentCommentId = null;
    if (isset($_POST['parentCommentId'])) {
        $parentCommentId = $_POST['parentCommentId'];
    }

    // create a comment object!
    $response = $client->addComment($_POST['name'], $_POST['email'], $_POST['comment'], $variables['id'], $parentCommentId);

    if (is_array($response)) {
        $response = json_encode($response);
    }

    echo $response;
}
<?php
/**
 * Pagewize image manager
 */
use PagewizeClient\PagewizeImageManager;

// include composer autoloader
include __DIR__ . '/vendor/autoload.php';

/** @var array $interventionImageConfig */
require_once __DIR__ . '/config.php';

// init variables
$width = null;
$height = null;
$fileType = null;
$blur = null;
$sourceImage = $_GET['src'];

if (isset($_GET['w'])) {
    $width = $_GET['w'];
}

if (isset($_GET['h'])) {
    $height = $_GET['h'];
}

if (isset($_GET['f'])) {
    $fileType = $_GET['f'];
}

if (isset($_GET['b'])) {
    $blur = $_GET['b'];
}

echo PagewizeImageManager::processImageRequest(
    $interventionImageConfig,
    $sourceImage,
    $width,
    $height,
    $fileType,
    $blur
);

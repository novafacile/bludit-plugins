<?php
// Security constant
define('BLUDIT', true);
define('DS', DIRECTORY_SEPARATOR);

// Load AJAX Helper Object
require 'AJAX.php';

// set JSON Header
AJAX::setHeader();

// auth
AJAX::auth();

// check parameters
if(!isset($_POST['album'])){
  AJAX::exit('400', 'Missing album in request.');
}

if(!isset($_POST['file'])){
  AJAX::exit('400', 'Missing file in request.');
}

$album = $_POST['album'];
$file = $_POST['file'];

// small security check
if(strpos($album, '..'.DS) !== false){
  AJAX::exit();
}

if(strpos($file, '..'.DS) !== false){
  AJAX::exit();
}

// check file
$basePath = dirname( __FILE__, 4); // Bludit3 Base
$storageRoot = 'imagegallery';
$cacheName = 'cache';
$albumDir = $basePath.DS.'bl-content'.DS.$storageRoot.DS.$_POST['album'];
$cacheDir = $albumDir.DS.$cacheName;
$fileThumb = $cacheDir.DS.'thumb'.DS.$file;
$fileLarge = $cacheDir.DS.'large'.DS.$file;
$fileOriginal = $albumDir.DS.$file;

if(!file_exists($fileOriginal)){
  AJAX::exit(404, 'File Not Found');
}

// perform delete
@unlink($fileThumb);
@unlink($fileLarge);
@unlink($cacheDir.DS.'files.php'); // clear files cache
$success = unlink($fileOriginal);

if($success){
  AJAX::exit(200);
} else {
  AJAX::exit(500);
}



?>
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

// check param
if(!isset($_POST['album'])){
  AJAX::exit('400', 'Missing album in request.');
}

$album = $_POST['album'];

// small security check
if(strpos($album, '..'.DS) !== false){
  AJAX::exit();
}

// check storage
$basePath = dirname( __FILE__, 4); // Bludit3 Base
$storage = $basePath.DS.'bl-content'.DS.'novagallery'.DS.$_POST['album'];
$cache = $storage.DS.'cache';

if(!file_exists($storage)){
  AJAX::exit(404, 'Album Not Found');
}

// perform upload
if (!empty($_FILES)) { 
    $fileName = $_FILES['file']['name'];
    $tempFile = $_FILES['file']['tmp_name'];
    $targetFile =  $storage.DS.$fileName;
    $success = move_uploaded_file($tempFile,$targetFile);


    // create thumb & large
    require $basePath.DS.'bl-kernel'.DS.'helpers'.DS.'image.class.php';
    $image = new Image();
    // todo: read config

    // thumb
    $thumb = [
      'cacheName' => 'thumb',
      'size' => 400,
      'format' => 'crop',
      'quality' => 80
    ];
    $set = $thumb;

    // check & create cache dir
    $cacheDir = $cache.DS.$set['cacheName'];
    if(!file_exists($cacheDir)){
      mkdir($cacheDir, 755, true);
    }
    // create image
    $image->setImage($targetFile, $set['size'], $set['size'], $set['format']);
    $image->saveImage($storage.DS.'cache'.DS.$set['cacheName'].DS.$fileName, $set['quality']);

    // large
    $large = [
      'cacheName' => 'large',
      'size' => 1000,
      'format' => 'auto',
      'quality' => 90
    ];
    $set = $large;
    // check & create cache dir
    $cacheDir = $cache.DS.$set['cacheName'];
    if(!file_exists($cacheDir)){
      mkdir($cacheDir, 755, true);
    }
    $image->setImage($targetFile, $set['size'], $set['size'], $set['format']);
    $image->saveImage($storage.DS.'cache'.DS.$set['cacheName'].DS.$fileName, $set['quality']);    

    // clear files cache
    @unlink($cache.DS.'files.php');

    // exit process
    if($success){
      AJAX::exit(200);  
    } else {
      AJAX::exit(500);
    }

} else {
  AJAX::exit(400, 'No files');
}

?>
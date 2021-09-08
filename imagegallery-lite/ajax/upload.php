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

// get plugin path
$pluginPath = dirname(pathinfo(__FILE__, PATHINFO_DIRNAME));

// set images storage
$basePath = dirname( __FILE__, 4); // Bludit3 Base
$storageRoot = 'imagegallery';
$storage = $basePath.DS.'bl-content'.DS.$storageRoot.DS.$_POST['album'];
$cache = $storage.DS.'cache';

if(!file_exists($storage)){
  AJAX::exit(404, 'Album Not Found');
}

// check cache dir
if(!file_exists($cache)){
  mkdir($cache, 0755);
}

// perform upload
if (!empty($_FILES)) { 
    $fileName = $_FILES['file']['name'];
    $tempFile = $_FILES['file']['tmp_name'];
    $file =  $storage.DS.$fileName;
    $success = move_uploaded_file($tempFile,$file);

    // default config
    // feature for pro version: read config form settings
    $imageSettings = [
      'thumb' => [
        'cacheName' => 'thumb',
        'size' => 400,
        'format' => 'crop',
        'quality' => 80
      ],
      'large' => [
        'cacheName' => 'large',
        'size' => 1500,
        'format' => 'auto',
        'quality' => 90
      ]
    ];

    // create thumb & large
    require $pluginPath.DS.'vendors'.DS.'SimpleImage.php';
    $image = new \claviska\SimpleImage();

    foreach ($imageSettings as $value) {
      $cacheName = $value['cacheName'];
      $cacheDir = $cache.DS.$cacheName;
      $cacheFile = $storage.DS.'cache'.DS.$cacheName.DS.$fileName;
      
      if(!file_exists($cacheDir)){
        mkdir($cacheDir, 0755);
      }

      $image
        ->fromFile($file)
        ->autoOrient();
      $mimeType = $image->getMimeType();
      
      switch ($value['format']) {
          case 'crop':
            $image->thumbnail($value['size'], $value['size']);
            break;          
          default:
            $image->bestFit($value['size'], $value['size']);
            break;
        }  

      $image->toFile($cacheFile, $mimeType, $value['quality']);

    }
  
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
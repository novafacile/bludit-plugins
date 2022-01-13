<?php defined('BLUDIT') or die('Bludit CMS.');
/**
 * AJAX requests helper functions for Bludit Image Gallery Lite
 * @author    novafacile OÜ
 * @copyright 2022 by novafacile OÜ
 * @license   AGPL-3.0
 * @see       https://bludit-plugins.com
 * This program is distributed in the hope that it will be useful - WITHOUT ANY WARRANTY.
 */
// check if dir or file exist
function checkFileDirExists($fileDir){
  if(file_exists($fileDir)) {
    return true;
  } else{
    AJAX::exit(404, 'File Not Found');
  }
}

function sanitizeFilename($filename){
  // https://stackoverflow.com/a/42058764
  // transform file or dir name white spaces and special chars to beautiful filename
  $filename = preg_replace('~[<>:"/\\\|?*]|[\x00-\x1F]|[\x7F\xA0\xAD]|[#\[\]@!$&\'()+,;=]|[{}^\~`]~x','-', $filename);
  $filename = ltrim($filename, '.-');
  $filename = preg_replace(array('/ +/','/_+/','/-+/'), '-', $filename);
  $filename = preg_replace(array('/-*\.-*/','/\.{2,}/'), '.', $filename);
  $filename = mb_strtolower($filename, mb_detect_encoding($filename));
  $filename = trim($filename, '.-');
  // cut length to 255 chars
  $ext = pathinfo($filename, PATHINFO_EXTENSION);
  $filename = mb_strcut(pathinfo($filename, PATHINFO_FILENAME), 0, 255 - ($ext ? strlen($ext) + 1 : 0), mb_detect_encoding($filename)) . ($ext ? '.' . $ext : '');
  return $filename;
}


// delete image
function deleteImage($storage, $album, $file){
  $cacheName = 'cache';
  $dir = $storage.DS.$album;
  @unlink($dir.DS.$cacheName.DS.'thumb'.DS.$file);  // delete thumbnail
  @unlink($dir.DS.$cacheName.DS.'large'.DS.$file);  // delete large image
  $success = unlink($dir.DS.$file);                  // delete original image
  @unlink($dir.DS.$cacheName.DS.'files.php');       // clear files cache
  @unlink($storage.DS.'cache'.DS.'files.php');       // clear global cache
  return $success;
}

// upload image
function uploadImage($pluginPath, $albumDir, $config){
  if (empty($_FILES)) {
    return false;
  }

  $imageSettings = [
    'thumb' => [
      'cacheName' => 'thumb',
      'size' => $config->getField('img-thumb-size'),
      'format' => 'crop',
      'quality' => $config->getField('img-thumb-quality')
    ],
    'large' => [
      'cacheName' => 'large',
      'size' => $config->getField('img-large-size'),
      'format' => 'auto',
      'quality' => $config->getField('img-large-quality')
    ]
  ];

  $fileName = sanitizeFilename($_FILES['file']['name']);
  $tempFile = $_FILES['file']['tmp_name'];
  $file = $albumDir.DS.$fileName;
  $cache = $albumDir.DS.'cache';
  $success = false;

  if(!file_exists($albumDir)) {
    mkdir($albumDir, 0755);
  }

  if(!file_exists($cache)) {
    mkdir($cache, 0755);
  }

  $success = move_uploaded_file($tempFile,$file);
  if(!$success) {
    return false;
  }

  // create thumb & large
  require $pluginPath.DS.'vendors'.DS.'SimpleImage.php';
  require $pluginPath.DS.'vendors'.DS.'novaImage.php';

  foreach ($imageSettings as $value) {
    $cacheName = $value['cacheName'];
    $cacheDir = $cache.DS.$cacheName;
    $cacheFile = $cacheDir.DS.$fileName;
    
    if(!file_exists($cacheDir)){
      mkdir($cacheDir, 0755);
    }

    $image = new \novafacile\novaImage($file);
    $image->resize($value['size'],$value['size'],$value['format']);
    $image->toFile($cacheFile, $value['quality']);
  }
  return true;
}

?>
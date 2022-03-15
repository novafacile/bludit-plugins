<?php
/**
 * Image Gallery Lite - Image Gallery for Bludit3
 * Image gallery object for user frontend
 * 
 * @author     novafacile OÜ
 * @copyright  2022 by novafacile OÜ
 * @license    AGPL-3.0
 * @see        https://bludit-plugins.com
 * @notes      based on PHP Image Gallery novaGallery - https://novagallery.org
 * This program is distributed in the hope that it will be useful - WITHOUT ANY WARRANTY.
 */
namespace novafacile;

class BluditImageGalleryLite {

  protected $gallery = null;
  protected $storageRoot = 'imagegallery';
  protected $maxCacheAge = 360;
  protected $onlyWithImages = true;
  protected $pathThumbnail = 'cache'.DS.'thumb'.DS;
  protected $pathLarge = 'cache'.DS.'large'.DS;
  protected $config = ['imagesSort' => 'newest'];
  
  function __construct($config, $adminView = false) {

    // set config
    $this->config = array_merge($this->config, $config);
    if($adminView) {
      $this->onlyWithImages = false;
      $this->maxCacheAge = false;
    }
  }

  protected function loadGallery($album = ''){
    if(is_null($this->gallery)){
      $storage = $this->storage($album);
      $this->gallery = new novaGallery($storage, $this->onlyWithImages, $this->maxCacheAge);
    }
  }

  protected function urlPath($album = ''){
    global $site;
    $url = $this->addSlash($site->url(), true);
    $path = $url.'bl-content/'.$this->storageRoot.'/'.$album;
    return $this->addSlash($path, true);
  }

  protected function storage($album){
    $path = PATH_CONTENT.$this->storageRoot.DS.$album;
    return $this->addSlash($path);
  }

  protected function addSlash($string, $urlPath = false){
    $delimiter = $urlPath?'/':DS;   // if urlPath always use '/' else use delimiter of filesystem
    $lastChar = substr($string, -1);
    if($lastChar != $delimiter){
      $string = $string.$delimiter;
    }
    return $string;
  }

  /**
   * Public method to load images
   **/
  public function images($album, $sort = 'default'){
    $this->loadGallery($album);
    $imagesList = $this->gallery->images($sort);
    $images = [];
    foreach ($imagesList as $image => $timestamp) {
      $images[$image]['filename'] = $image;
      $images[$image]['thumbnail'] = $this->urlPath($album).$this->pathThumbnail.$image;
      $images[$image]['large'] = $this->urlPath($album).$this->pathLarge.$image;
    }
    return $images;
  }

}
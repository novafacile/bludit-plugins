<?php
/**
 *  Image Gallery - Simple Image Gallery for Bludit3
 *
 *  @package    Bludit
 *  @subpackage novaGallery Lite
 *  @category   Plugins
 *  @author     novafacile OÜ
 *  @copyright  2021 by novafacile OÜ
 *  @license    AGPL-3.0
 *  @version    1.0.0-beta
 *  @see        https://github.com/novafacile/bludit-plugins
 *  @release    2021-03-27
 *  @notes      based on PHP Image Gallery novaGallery - https://novagallery.org
 *  This program is distributed in the hope that it will be useful - WITHOUT ANY WARRANTY.
 *
 */

class pluginNovaGalleryLite extends Plugin {

  // init plugin
  public function init() {
    $this->dbFields = array(
      'gallery-title' => '',
      'page' => '',
      'protect-storage' => false
    );

  }

  public function install($position = 1): bool {
    parent::install($position);
    // create storage or storage protection
    if(file_exists($this->storage('album'))){
      // Todo: set storage as protected if already exists
      // $this->dbFields['protect-storage'] = true; // <-- this doesn't work
    } else {
      Filesystem::mkdir($this->storage('album'), true);
    }
    return $this->save();
  }

  public function uninstall(): bool {
    parent::uninstall();
    // delete storage
    if(!$this->getValue('protect-storage')){
      Filesystem::deleteRecursive($this->storage());  
    }
    return true;
  }

  public function adminHead(){
    global $url;  
    if (!$url->slug() == $this->pluginSlug()) {
      return false;
    }
    $html = $this->includeCSS('dropzone.min.css');
    $html .= $this->includeCSS('simple-lightbox.min.css');
    return $html;
  }

  public function adminBodyEnd(){
    global $security, $url, $L;
    if (!$url->slug() == $this->pluginSlug()) {
      return false;
    }

    $pluginUrl = $this->pluginUrl();
    
    $html = $this->includeJS('dropzone.min.js');
    $html .= $this->includeJS('simple-lightbox.min.js');
    $html .= '<script>
              Dropzone.autoDiscover = false;
              var imageGalleryUpload = new Dropzone("div#novagallery-upload", {
                url: "'.$this->domainPath().'ajax/upload.php",
                params: {
                  "tokenCSRF": "'.$security->getTokenCSRF().'",
                  "album": "album"
                },
                addRemoveLinks: true,
                acceptedFiles: ".jpg,.jpeg,.png",
                dictDefaultMessage: "<b>'.$L->get('Drop files here or click to upload.').'</b><br><br>('.$L->get('Upload will start immediately.').').",
                dictFileTooBig: "'.$L->get('File is to big. Max. file size:').' {{maxFilesize}} MiB",
                dictInvalidFileType: "'.$L->get('This is not a JPEG or PNG.').'",
                dictResponseError: "{{statusCode}} '.$L->get('Server error during upload.').'",
                dictCancelUpload: "'.$L->get('Cancel upload').'",
                dictUploadCanceled: "'.$L->get('Upload canceled').'",
                dictCancelUploadConfirmation: "'.$L->get('Cancel upload?').'",
                dictRemoveFile: "'.$L->get('Remove').'"
              });
              imageGalleryUpload.on("queuecomplete", function() { $("#novagallery-reload-button").removeClass("d-none"); });
              imageGalleryUpload.on("addedfile", function(file) { $("#novagallery-reload-button").addClass("d-none"); });
              </script>';
    $html .= $this->includeJS('novagallery.js');
    return $html;

  }

  // Shortcut on sidebar
  public function adminSidebar() {
    global $login, $L;
    if ($login->role() === 'admin' || $login->role() === 'author') {
      return '<a class="nav-link" href="'.$this->pluginUrl().'">'.$L->get('Image Gallery').'</a>';
    } else {
      return '';
    }
  }

  // config form
  public function form() {
    global $L, $staticPages;

    /*** form start ***/
    $html = "\n";
    $html .= '<style type="text/css">
            .plugin-form .novagallery-form label {margin-top: 0 !important; }
            .plugin-form .novagallery-form .short-input { max-width: 200px };
            </style>';

    /*** tab navi ***/
    $html .= '<div class="tab-content novagallery-form" id="nav-tabContent">';
    $html .= '<nav class="mb-3">
      <div class="nav nav-tabs" id="nav-tab" role="tablist">
        <a class="nav-item nav-link active" id="nav-novagallery-image-tab" data-toggle="tab" href="#novagallery-images" role="tab" aria-controls="nav-novagallery-images" aria-selected="true">'.$L->get('Images').'</a>
        <a class="nav-item nav-link" id="nav-novagallery-settings-tab" data-toggle="tab" href="#novagallery-settings" role="tab" aria-controls="nav-novagallery-settings" aria-selected="false">'.$L->get('Settings').'</a>
      </div>
    </nav>
    ';

    /*** Images ***/
    $html .= '<div class="tab-pane fade show active" id="novagallery-images" role="tabpanel" aria-labelledby="novagallery-image-tab">';

    // Upload
    $html .= Bootstrap::formTitle(array('title' => '<i class="fa fa-upload"></i> '.$L->get('Upload')));
    $html .= '<div class="dropzone mb-2" id="novagallery-upload" style="border-style:dotted;"></div>';
    $html .= '<div class="w-100 text-center mb-5"><a href="'.$this->pluginUrl().'" class="d-none btn btn-primary px-4" id="novagallery-reload-button">'.$L->get('Reload page').'</a></div>';

    // Image List
    $html .= Bootstrap::formTitle(array('title' => '<i class="fa fa-image"></i> '.$L->get('Images')));
    $html .= $this->outputImages('album');
    // close images
    $html .= '</div>';



   /*** Settings ***/
    $html .= '<div class="tab-pane fade" id="novagallery-settings" role="tabpanel" aria-labelledby="novagallery-settings-tab">';
    $html .= Bootstrap::formTitle(array('title' => $L->get('Settings')));
    $html .= '<p>'.$L->get('Settings for novaGallery Lite').'</p>';


    // gallery name
    $html .= Bootstrap::formInputText(array(
              'name' => 'gallery-title',
              'label' => $L->get('Gallery Title'),
              'value' => $this->getValue('gallery-title'),
              'tip' => $L->get('This is the title of the gallery shown in frontend (optional).')
              ));


    // select page where gallery is shown
    $options = array('' => '');
    try { // true to get page name of saved page
      $pluginPage = $this->getValue('page');
      if($pluginPage){
        $page = new Page($pluginPage);
        $options[$pluginPage] = $page->title();
      }
    } catch (Exception $e){
      // continue
    }

    $html .= Bootstrap::formSelect(array(
      'name' => 'page',
      'label' => $L->get('Image Gallery Page'),
      'options' => $options,
      'selected' => $this->getValue('page'),
      'tip'=> $L->get('Only published content appears in this list. Remove the selected page to deactivate the image gallery.')
    ));
    
    $html .= '<script>
    $(document).ready(function() {
      var pageSelect = $("#jspage");
      pageSelect.select2({
        placeholder: "'.$L->get('Start typing to see a list of suggestions.').'",
        allowClear: true,
        theme: "bootstrap4",
        minimumInputLength: 2,
        ajax: {
          url: "'.HTML_PATH_ADMIN_ROOT.'ajax/get-published",
          data: function (params) {
            var query = { query: params.term }
            return query;
          },
          processResults: function (data) {
            return data;
          }
        },
        escapeMarkup: function(markup) {
          return markup;
        }
      })
    });
    </script>';

    // prevent storage deletion on uninstall
    $html .= Bootstrap::formSelect(array(
              'name' => 'protect-storage',
              'label' => $L->get('Protect Image Storage'),
              'class' => 'short-input',
              'options' => array(
                false => $L->get('deactivate'),
                true => $L->get('activate'),
              ),
              'selected' => $this->getValue('protect-storage'),
              'tip'=> $L->get('If activated, the stored images will not be deleted on unsinstall.')
              ));

    // close settings tab
    $html .= '</div>';


    /*** form end ***/
    $html .= '</div>';
    return $html;
  }




  /*********
   * Frontend methods
   **********/

  // Load image by cache
  public function beforeAll(){
    if(!$this->webhook($this->webhookImages(),true, false)) {
      return;
    }

    $request = $_SERVER['REQUEST_URI'];
    $galleryPath = '/'.$this->webhookImages().'/';
    $imagePath = str_ireplace($galleryPath, '', $request);

    $splitPath = explode('/', $imagePath);

    $image = array_pop($splitPath);
    $size = array_pop($splitPath);
    $cacheName = array_pop($splitPath);
    $album = implode('/', $splitPath);
    $storage = $this->storage();
    $file = $storage.$album.DS.$image;
    $cacheDir = $storage.$album.DS.$cacheName.DS.$size;
    $cacheFile = $cacheDir.DS.$image;
    $type = strtolower(pathinfo($file, PATHINFO_EXTENSION));

    // check if original file exists
    if(!file_exists($file)){
      return;
    }

    // check if cached file already exists
    if(file_exists($cacheFile)){
      readfile($cacheFile);
      exit;
    }

    // get settings for image
    $set = [
      'thumb' => [
        'size' => 400,
        'quality' => 80,
        'format' => 'crop'
      ],
      'large' => [
        'size' => 2000,
        'quality' => 90,
        'format' => 'auto'
      ]
    ];

    if($size !== 'thumb' && $size !== 'large'){
      die('Bad request');
    }

    $set = $set[$size];
    
    if(!file_exists($cacheDir)){
      mkdir($cacheDir, 777, true);
    }

    $image = new Image();
    $image->setImage($file, $set['size'], $set['size'], $set['format']);
    $image->saveImage($cacheFile, $set['quality']);

    switch ($type) {
      case 'jpg':
        $headerType = 'image/jpeg';
        break;
      case 'jpeg':
        $headerType = 'image/jpeg';
        break;
      case 'png':
        $headerType = 'image/png';
        break;
      default:
        die('Bad Request');
        break;
    }

    header('Content-Type:'.$type);
    header('Content-Length: ' . filesize($cacheFile));
    readfile($cacheFile);
    exit;
  }



  // Load CSS for gallery
  public function siteHead() {
    if($this->webhook($this->webhookUrl())) {
      $html = '';
      $html = $this->includeCSS('dropzone.min.css');
      $html .= $this->includeCSS('simple-lightbox.min.css');
      
      $css = THEME_DIR_CSS . 'novagallery.css';
      if(file_exists($css)) {
        $html .= Theme::css('css' . DS . 'novagallery.css');
      } else {
        $html .= '<link rel="stylesheet" href="' .$this->htmlPath(). 'layout' . DS . 'novagallery.css">' .PHP_EOL;
      }

      return $html;
    }
  } 


  // Load JS for gallery
  public function siteBodyEnd() {
    if($this->webhook($this->webhookUrl())) {
      $html = '';
      $html = $this->includeJS('dropzone.min.js');
      $html .= $this->includeJS('simple-lightbox.min.js');
      $html .= '<script>var lightbox = new SimpleLightbox(".novagallery .image", {});</script>';

      return $html;
    }
  }


  // Load gallery
  public function pageEnd(){
    if(!$this->webhook($this->webhookUrl())){
      return;
    }

    $album = 'album';
    $sort = 'newest';

    $images = $this->images($album, $sort);
    $path = $this->storage($album, true);
    $pathThumbnail = $path.'/cache/thumb/';
    $pathLarge = $path.'/cache/large/';

    $template = THEME_DIR_PHP . 'novagallery.php';
    if(file_exists($template)) {
      include($template);
    } else {
      include(__DIR__ . DS . 'layout' . DS . 'novagallery.php');
    }   

  }


  /****
   * global methods
   *****/
  
  private function webhookUrl(){
    global $site;
    $pagePrefix = $site->getField('uriPage');
    $pagePrefix = ltrim($pagePrefix, '/');
    return $pagePrefix.$this->getValue('page');
  }

  private function webhookImages(){
    global $site;
    $pagePrefix = $site->getField('uriPage');
    $pagePrefix = ltrim($pagePrefix, '/');
    return $pagePrefix.'bl-content/novagallery';
  }

  private function addSlash($string){
    $lastChar = substr($string, -1);
    if($lastChar != '/'){
      $string = $string.'/';
    }

    return $string;
  }

  // path for images storage
  private function storage($album = '', $htmlPath = false){
    if($htmlPath){
      global $site;
      $url = $this->addSlash($site->url());
      $path = $url.'bl-content/novagallery/'.$album;
    } else {
      $path = PATH_CONTENT.'novagallery'.DS.$album;
    }

    return $this->addSlash($path);
  }

  private function images($album = '', $sort = 'default', $onlyWithImages = true, $maxCacheAge = 60){
    require 'vendors/novaGallery.php';
    $storage = $this->storage($album);
    $gallery = new novaGallery($storage, $onlyWithImages, $maxCacheAge);
    $images = $gallery->images($sort);
    return $images;
  }


  /****
   * admin methods
   *****/
  private function pluginUrl(){
    return HTML_PATH_ADMIN_ROOT.'configure-plugin/'.$this->className();
  }

  private function pluginSlug(){
    return 'configure-plugin/'.$this->className();
  }

  public function isAllowed(){
    global $login;
    $role = $login->role();
    if($role === 'admin' || $role === 'author' || $role === 'editor'){
      return true;
    } else {
      return false;
    }
  }

  public function outputImages($album){
    if (!$this->isAllowed()) {
      return false;
    }

    global $L;
    $images = $this->images($album, 'default', false, false);
    $path = $this->storage($album, true);
    $pluginUrl = $this->pluginUrl();
    $html = '<div class="row w-100 text-left">';
    $i = 0;
    foreach ($images as $image => $timestamp) {
      $html .= '<div class="col-3 mb-5 text-break novagallery-images text-center" id="novagallery-image-'.++$i.'">
                  <a href="'.$path.'cache/large/'.$image.'" class="image">
                    <img src="'.$path.'cache/thumb/'.$image.'" style="max-width: 100%;max-height:300px;">
                  </a>
                  <div class="text-left">'.$image.'<br>
                    <i class="fa fa-trash novagallery-del-file" style="cursor:pointer"
                      data-url="'.$this->domainPath().'" 
                      data-album="'.$album.'" 
                      data-file="'.$image.'"
                      data-number="'.$i.'"
                      title="'.$L->get('Delete Image').'"></i>
                  </div>
                </div>';
    }
    $html .= '</div>';
    return $html;
  }

}
<?php
/**
 * Image Gallery Lite - Image Gallery for Bludit3
 *
 * @package    Bludit
 * @subpackage ImageGallery Lite
 * @category   Plugins
 * @author     novafacile OÜ
 * @copyright  2022 by novafacile OÜ
 * @license    AGPL-3.0
 * @version    1.4.0
 * @see        https://bludit-plugins.com
 * @release    2022-01-13
 * @notes      based on PHP Image Gallery novaGallery - https://novagallery.org
 * This program is distributed in the hope that it will be useful - WITHOUT ANY WARRANTY.
 */

class pluginImageGalleryLite extends Plugin {

  private $storageRoot = 'imagegallery';
  private $gallery = false;
  private $maxCacheAge = 86400;

  // init plugin
  public function init() {
    $this->dbFields = array(
      'gallery-title' => '',
      'page' => '',
      'lightbox-theme' => 'white',
      'protect-storage' => false,
      'img-thumb-size' => 400,
      'img-thumb-quality' => 80,
      'img-large-size' => 1500,
      'img-large-quality' => 90,
      'default-image-sort' => 'newest',
    );
  }

  public function install($position = 1): bool {
    parent::install($position);
    // create storage or storage protection
    $storage = PATH_CONTENT.$this->storageRoot.DS;
    if(file_exists($storage)){
      $this->db['protect-storage'] = true;
    } else {
      Filesystem::mkdir($storage, true);
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
    $html .= $this->includeCSS('jquery-confirm.min.css');
    $html .= $this->includeCSS('imagegallery-admin.css');
    return $html;
  }

  public function adminBodyEnd(){
    global $url;
    if (!$url->slug() == $this->pluginSlug()) {
      return false;
    }
    $album = 'album';
    $domainPath = $this->domainPath();

    // get helper object
    require_once('app/BluditImageGalleryHelper.php');
    $helper = new \novafacile\BluditImageGalleryHelper();

    // load required JS
    $html = $this->includeJS('simple-lightbox.min.js');
    $html .= $this->includeJS('jquery-confirm.min.js');
    $html .= $helper->adminJSData($domainPath);
    if($album){
      $html .= $this->includeJS('dropzone.min.js');
      $html .= $helper->dropzoneJSData($album);
    }
    $html .= $this->includeJS('imagegallery-admin.js');
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

    // Load Settings
    require_once('vendors/novaGallery.php');
    require_once('app/BluditImageGallery.php');
    require_once('app/BluditImageGalleryAdmin.php');
    $album = 'album';
    $config['imagesSort'] = $this->getValue('default-image-sort');
     
    // load gallery
    $gallery = new novafacile\BluditImageGalleryAdmin($config, true);

    /*** form start ***/
    $html = "\n";
    $html .= '<style type="text/css">
            .plugin-form .imagegallery-form label {margin-top: 0 !important; }
            .plugin-form .imagegallery-form .short-input { max-width: 200px };
            </style>';

    /*** tab navi ***/
    $html .= '<div class="tab-content imagegallery-form" id="nav-tabContent">';
    $html .= '<nav class="mb-3">
      <div class="nav nav-tabs" id="nav-tab" role="tablist">
        <a class="nav-item nav-link active" id="nav-imagegallery-image-tab" data-toggle="tab" href="#imagegallery-images" role="tab" aria-controls="nav-imagegallery-images" aria-selected="true">'.$L->get('Images').'</a>
        <a class="nav-item nav-link" id="nav-imagegallery-settings-tab" data-toggle="tab" href="#imagegallery-settings" role="tab" aria-controls="nav-imagegallery-settings" aria-selected="false">'.$L->get('Settings').'</a>
        <!-- <a class="nav-item nav-link" id="nav-imagegallery-pro-settings-tab" data-toggle="tab" href="#imagegallery-pro-settings" role="tab" aria-controls="nav-imagegallery-pro-settings" aria-selected="false">'.$L->get('ImageGallery Pro').'</a> -->
      </div>
    </nav>
    ';

    /*** Images ***/
    $html .= '<div class="tab-pane fade show active" id="imagegallery-images" role="tabpanel" aria-labelledby="imagegallery-image-tab">';
    $album = 'album';
    
    // Upload
    $html .= Bootstrap::formTitle(array('title' => '<i class="fa fa-upload mt-4"></i> '.$L->get('Upload')));
    $html .= '<p>'.$L->get('File names of uploaded images will be modified for maximum compatibility (e.g. no spaces & special characters, lower case). Images with the same file name will be overwritten.').'</p>';
    $html .= '<div class="dropzone mb-2" id="imagegallery-upload" style="border-style:dotted;"></div>';
    $html .= '<div class="w-100 text-center mb-5"><a href="'.$_SERVER['REQUEST_URI'].'" class="d-none btn btn-primary px-4" id="imagegallery-reload-button">'.$L->get('Reload page').'</a></div>';
  
    // Image List
    $html .= Bootstrap::formTitle(array('title' => '<i class="fa fa-image"></i> '.$L->get('Images')));
    $html .= $gallery->outputImagesAdmin($album);
  
    // close images
    $html .= '</div>';


   /*** Settings ***/
    $html .= '<div class="tab-pane fade" id="imagegallery-settings" role="tabpanel" aria-labelledby="imagegallery-settings-tab">';
    $html .= Bootstrap::formTitle(array('title' => $L->get('Settings')));
    $html .= '<p>'.$L->get('Settings for ImageGallery').'</p>';

    // gallery name
    $html .= Bootstrap::formInputText(array(
              'name' => 'gallery-title',
              'label' => $L->get('Gallery Title'),
              'value' => $this->getValue('gallery-title'),
              'tip' => $L->get('This is the title of the gallery shown in frontend (optional).')
              ));

    // select page where gallery is shown
    $pluginPageOptions = [];
    $pluginPage = $this->getValue('page');
    if($pluginPage){
      $pluginPageOptions[$pluginPage] = $this->getPageTitle($pluginPage);
    }
    $html .= Bootstrap::formSelect(array(
      'name' => 'page',
      'label' => $L->get('Image Gallery Page'),
      'options' => $pluginPageOptions,
      'selected' => $this->getValue('page'),
      'tip'=> $L->get('Only published content appears in this list. Remove the selected page to deactivate the image gallery.')
    ));
    
    // set lightbox theme
    $html .= Bootstrap::formSelect(array(
              'name' => 'lightbox-theme',
              'label' => $L->get('Lightbox Theme'),
              'class' => 'short-input',
              'options' => array(
                'default' => $L->get('Default'),
                'grey' => $L->get('Grey'),
                'black' => $L->get('Black'),
                'custom' => $L->get('Custom (Pro Only)')
              ),
              'selected' => $this->getValue('lightbox-theme'),
              'tip'=> $L->get('Default is "White". If a custom CSS file is used for the image gallery, "Default" should be selected. In the Pro version, custom colors can be set in the ImageGallery Pro settings.')
              ));

    // Default Image Sort
    $html .= Bootstrap::formSelect(array(
              'name' => 'default-image-sort',
              'label' => $L->get('Default Image Sort'),
              'class' => 'short-input',
              'options' => array(
                'newest' => $L->get('Newest First'),
                'oldest' => $L->get('Oldest First'),
                'a-z' => $L->get('Alphabetical (A-Z)'),
              ),
              'selected' => $this->getValue('default-image-sort'),
              'tip'=> $L->get('This defines how the images are sorted. Sorting by "age" is based on EXIF data in the images. If the EXIF data is missing, this kind of sorting may not work correctly.')
              ));
    $html .= '<div class="pb-3"></div>';

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
              'tip'=> $L->get('If activated, the stored images will not be deleted on uninstall.')
              ));



    // image settings: thumbnail size & quality
    $html .= Bootstrap::formTitle(array('title' => $L->get('Image Settings')));
    $html .= '<p>'.$L->get('Settings will only effects future uploads.').'</p>';
    $html .= Bootstrap::formInputText(array(
            'name' => 'img-thumb-size',
            'class' => 'short-input " type="number',
            'label' => $L->get('Thumbnail Size'),
            'value' => $this->getValue('img-thumb-size'),
            'tip' => $L->get('Max. width or height in pixel.')
            ));
    $html .= Bootstrap::formInputText(array(
            'name' => 'img-thumb-quality',
            'class' => 'short-input " type="number',
            'label' => $L->get('Thumbnail Quality'),
            'value' => $this->getValue('img-thumb-quality'),
            'tip' => $L->get('Image quality for thumbnails (1-100). Higher quality effects higher file size.')
            ));

    // large size & quality
    $html .= Bootstrap::formInputText(array(
            'name' => 'img-large-size',
            'class' => 'short-input " type="number',
            'label' => $L->get('Large Image Size'),
            'value' => $this->getValue('img-large-size'),
            'tip' => $L->get('Max. width or height in pixel.')
            ));
    $html .= Bootstrap::formInputText(array(
            'name' => 'img-large-quality',
            'class' => 'short-input " type="number',
            'label' => $L->get('Large Images Quality'),
            'value' => $this->getValue('img-large-quality'),
            'tip' => $L->get('Image quality for large image (1-100), viewed in lightbox. Higher quality effects higher file size.')
            ));

    // close settings tab
    $html .= '</div>';

    /*** Settings for Pro ***/
    //$html .= '<div class="tab-pane fade" id="imagegallery-pro-settings" role="tabpanel" aria-labelledby="imagegallery-pro-settings-tab">';
    
    // Todo: Info about ImageGallery Pro

    // close settings for pro tab
    //$html .= '</div>';

    /*** form end ***/
    $html .= '</div>';
    return $html;
  }

  /*********
   * Frontend methods
   **********/

  // Load CSS for gallery
  public function siteHead() {
    if($this->webhookAlbum() !== false) {
      $html = $this->includeCSS('simple-lightbox.min.css');
      
      $css = THEME_DIR_CSS . 'imagegallery.css';
      if(file_exists($css)) {
        $html .= Theme::css('css/imagegallery.css');
      } else {
        $html .= '<link rel="stylesheet" href="' .$this->htmlPath(). 'layout/imagegallery.css">' .PHP_EOL;
      }

      // custom css settings
      $html .= '<style>'.PHP_EOL;
      $html .= $this->lightboxCSS($this->getValue('lightbox-theme'));
      $html .= '</style>'.PHP_EOL;

      return $html;
    }
  } 

  // Load JS for gallery
  public function siteBodyEnd() {
    if($this->webhookAlbum() !== false){
      $html = $this->includeJS('simple-lightbox.min.js');
      $html .= '<script>var lightbox = new SimpleLightbox(".imagegallery .imagegallery-image .imagegallery-image-link", {});</script>';
      return $html;
    }
  }

  // Load gallery
  public function pageEnd(){
    $album = $this->webhookAlbum();
    if($album === false){
      return;
    }

    // load configs
    global $page, $L;
    $config = [
     'imagesSort' => $this->getValue('default-image-sort'),
     'galleryPage' => $page->slug()
    ];

    // load classes
    require_once('vendors/novaGallery.php');
    require_once('app/BluditImageGallery.php');
    $gallery = new novafacile\BluditImageGallery($config);

   
    $images = $gallery->images($album, $config['imagesSort']);

    // stop if no images in album or no albums
    if(empty($images)){
      return false;
    }

    $templateFile = 'imagegallery.php';

    // Check if custom template is available
    $template = THEME_DIR_PHP . $templateFile;
    if(file_exists($template)) {
      include($template);
    } else {
      include(__DIR__ . DS . 'layout' . DS . $templateFile);
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

  private function webhookAlbum(){
    if($this->webhook($this->webhookUrl())){
      return 'album';
    }
  }

  private function lightboxCSS($theme){
    if($theme == 'default'){
      return false;
    }
    switch ($theme) {
      case 'grey':
        $background = '#555555';
        $buttons = '#ffffff';
        break;

      case 'black':
        $background = '#000000';
        $buttons = '#dddddd';
        break;

      default:
        $background = '#ffffff';
        $buttons = '#333333';
        break;
    }

    return '.sl-overlay{background:'.$background.';}.sl-wrapper .sl-close,.sl-wrapper .sl-counter,.sl-wrapper .sl-navigation button{color:'.$buttons.'}';
  }

  /****
   * admin helper methods
   *****/
  private function pluginUrl(){
    return HTML_PATH_ADMIN_ROOT.'configure-plugin/'.$this->className();
  }

  private function pluginSlug(){
    return 'configure-plugin/'.$this->className();
  }

  private function getPageTitle($key){
    try { // try to get page name of page key
      $page = new Page($key);
      return $page->title();
    } catch (Exception $e){
      return '';
    }
  }

}
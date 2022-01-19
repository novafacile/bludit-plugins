<?php
/**
 * Online Store for Bludit3
 *
 * @package    Bludit
 * @subpackage Online Store by Ecwid
 * @category   Plugins
 * @author     novafacile OÜ
 * @copyright  2022 by novafacile OÜ
 * @license    MIT
 * @version    1.1.0
 * @see        https://bludit-plugins.com
 * @release    2022-01-19
 * This program is distributed in the hope that it will be useful - WITHOUT ANY WARRANTY.
 */

class pluginOnlineStoreEcwid extends Plugin {

  public function init() {
    $this->dbFields = [
      'storeId'		=> '',
      'page'	=> ''
    ];
  }

  // Method called on plugin settings on the admin area
  public function form() {
    global $L;
    $html = "\n";
    $html .= '<style type="text/css">
            .plugin-form .onlinestore-form label {margin-top: 0 !important; }
            .onlinestore-form .short-input { max-width: 200px };
            </style>';

    // tab navi
    $html .= '<div class="tab-content onlinestore-form" id="nav-tabContent">';
    $html .= '<nav class="mb-3">
      <div class="nav nav-tabs" id="nav-tab" role="tablist">
        <a class="nav-item nav-link active" id="nav-onlinestore-general-tab" data-toggle="tab" href="#onlinestore-general" role="tab" aria-controls="nav-onlinestore-general" aria-selected="true">'.$L->get('General').'</a>
      </div>
    </nav>
    ';

		// basics
    $html .= '<div class="tab-pane fade show active" id="onlinestore-general" role="tabpanel" aria-labelledby="onlinestore-general-tab">';
    $html .= Bootstrap::formTitle(array('title' => $L->get('General')));

    // select page where Online Store is shown
    $options = array('' => '');
    try { // true to get page name of saved page
      $storePage = $this->getValue('page');
      if($storePage){
        $page = new Page($storePage);
        $options[$storePage] = $page->title();
      }
    } catch (Exception $e){
      // continue
    }

    $html .= Bootstrap::formSelect(array(
      'name' => 'page',
      'label' => $L->get('Store Page'),
      'options' => $options,
      'selected' => $this->getValue('page'),
      'tip'=> $L->get('Only published content appears in this list. Remove the selected page to deactivate the online store.')
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

    $html .= Bootstrap::formInputText([
          'name' => 'storeId',
          'label' => $L->get('Store ID'),
          'class' => 'short-input',
          'value' => $this->getValue('storeId'),
          'tip' => $L->get('Store ID of your Ecwid Online Store. Instruction see below.')
          ]);


    $html .= '<div class="alert alert-info mt-5">
  							<h4 class="alert-heading">'.$L->get('Free Online Store by Ecwid').'</h4>
  							<p>
  								'.$L->get('To get a Store ID, you need an account at Ecwid.com. With this online store provider you can create an online store for free with all the necessary features with just a few clicks and manage your products.').'<br><br>
  								<a href="http://go.ecwid.com/bludit-onlinestore-register" target="_blank" class="btn btn-success">'.$L->get('Register For Free').'</a>
  								<a href="http://go.ecwid.com/bludit-onlinestore-login" target="_blank" class="btn btn-info">'.$L->get('Manage Online Store').'</a>
  							</p>
  							<hr>
  							<p class="mb-0 small text-muted">
  								'.$L->get('For payment processing you also need e.g. a Paypal account or that of another payment provider that can be connected to an Ecwid online store. Instructions and help can be found in the <a href="http://go.ecwid.com/bludit-onlinestore-help" target="_blank">Ecwid Help Center</a>.').'
  							</p>
								</div>
    				';

    $html .= '<div class="alert alert-info mt-5">
	    					<h4>'.$L->get('How to Get Store ID').'</h4>
	    					<ol>
	    						<li><a href="http://go.ecwid.com/bludit-onlinestore-login" target="_blank">'.$L->get('Login into your Ecwid account').'</a></li>
	    						<li>'.$L->get('Scroll down to page bottom').'</li>
	    						<li>'.$L->get('Copy Store ID').'</li>
	    					</ol>
	    					<h5>'.$L->get('Example').'</h5>
	    					<p><img src="'.$this->domainPath().'assets/get-store-id.jpg" class="border border-dark img-fluid" /></p>
    					</div>';


    return $html;
  }


  // Shortcut on sidebar
  public function adminSidebar() {
    global $login, $L;
    if ($login->role() === 'admin' || $login->role() === 'author') {
      return '<a class="nav-link" href="http://go.ecwid.com/bludit-onlinestore-login" target="_blank">'.$L->get('Manage Online Store').' <i class="fa fa-external-link"></i></a>';
    } else {
      return '';
    }
  }

  private function getStoreTag(){
  	$tag = '<div id="my-store-'.$this->getValue('storeId').'"></div>
						<div>
						<script data-cfasync="false" type="text/javascript" src="https://app.ecwid.com/script.js?'.$this->getValue('storeId').'&data_platform=code&data_date=2022-01-17" charset="utf-8"></script><script type="text/javascript"> xProductBrowser("categoriesPerRow=3","views=grid(20,3) list(60) table(60)","categoryView=grid","searchView=list","id=my-store-'.$this->getValue('storeId').'");</script>
						</div>';
		return $tag;
  }

  /** 
   * frontend methods 
   **/
  public function beforeSiteLoad() {
  	if($GLOBALS['WHERE_AM_I']=='page') {
  		$GLOBALS['page']->setField('content', $this->getStoreTag());
  	}
  }

  /**
   * global methods
   **/
  private function webhookUrl(){
    global $site;
    $pagePrefix = $site->getField('uriPage');
    $pagePrefix = ltrim($pagePrefix, '/');
    return $webhook = $pagePrefix.$this->getValue('page');
  }

}
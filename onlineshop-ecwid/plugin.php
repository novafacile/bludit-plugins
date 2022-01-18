<?php
/**
 * OnlineShop for Bludit3
 *
 * @package    Bludit
 * @subpackage OnlineShop Ecwid
 * @category   Plugins
 * @author     novafacile OÜ
 * @copyright  2022 by novafacile OÜ
 * @license    MIT
 * @version    1.0.0
 * @see        https://bludit-plugins.com
 * @release    2022-01-18
 * This program is distributed in the hope that it will be useful - WITHOUT ANY WARRANTY.
 */

class pluginOnlineShopEcwid extends Plugin {

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
            .plugin-form .onlineshop-form label {margin-top: 0 !important; }
            .onlineshop-form .short-input { max-width: 200px };
            </style>';

    // tab navi
    $html .= '<div class="tab-content onlineshop-form" id="nav-tabContent">';
    $html .= '<nav class="mb-3">
      <div class="nav nav-tabs" id="nav-tab" role="tablist">
        <a class="nav-item nav-link active" id="nav-onlineshop-general-tab" data-toggle="tab" href="#onlineshop-general" role="tab" aria-controls="nav-onlineshop-general" aria-selected="true">'.$L->get('General').'</a>
      </div>
    </nav>
    ';

		// basics
    $html .= '<div class="tab-pane fade show active" id="onlineshop-general" role="tabpanel" aria-labelledby="onlineshop-general-tab">';
    $html .= Bootstrap::formTitle(array('title' => $L->get('General')));

    // select page where Online Shop is shown
    $options = array('' => '');
    try { // true to get page name of saved page
      $shopPage = $this->getValue('page');
      if($shopPage){
        $page = new Page($shopPage);
        $options[$shopPage] = $page->title();
      }
    } catch (Exception $e){
      // continue
    }

    $html .= Bootstrap::formSelect(array(
      'name' => 'page',
      'label' => $L->get('Shop Page'),
      'options' => $options,
      'selected' => $this->getValue('page'),
      'tip'=> $L->get('Only published content appears in this list. Remove the selected page to deactivate the online shop.')
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
          'tip' => $L->get('Store ID of your Ecwid Online Shop. Instruction see below.')
          ]);


    $html .= '<div class="alert alert-info mt-5">
  							<h4 class="alert-heading">'.$L->get('Free Online Shop by Ecwid').'</h4>
  							<p>
  								'.$L->get('To get a Store ID, you need an account at Ecwid.com. With this online store provider you can create an online store for free with all the necessary features with just a few clicks and manage your products.').'<br><br>
  								<a href="http://go.ecwid.com/bludit-onlineshop-register" target="_blank" class="btn btn-success">'.$L->get('Register For Free').'</a>
  								<a href="http://go.ecwid.com/bludit-onlineshop-login" target="_blank" class="btn btn-info">'.$L->get('Manage Online Shop').'</a>
  							</p>
  							<hr>
  							<p class="mb-0 small text-muted">
  								'.$L->get('For payment processing you also need e.g. a Paypal account or that of another payment provider that can be connected to an Ecwid online store. Instructions and help can be found in the <a href="http://go.ecwid.com/bludit-onlineshop-help" target="_blank">Ecwid Help Center</a>.').'
  							</p>
								</div>
    				';

    $html .= '<div class="alert alert-info mt-5">
	    					<h4>'.$L->get('How to Get Store ID').'</h4>
	    					<ol>
	    						<li><a href="http://go.ecwid.com/bludit-onlineshop-login" target="_blank">'.$L->get('Login into your Ecwid account').'</a></li>
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
      return '<a class="nav-link" href="http://go.ecwid.com/bludit-onlineshop-login" target="_blank">'.$L->get('Manage Online Shop').' <i class="fa fa-external-link"></i></a>';
    } else {
      return '';
    }
  }

  private function getShopTag(){
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
  		$GLOBALS['page']->setField('content', $this->getShopTag());
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
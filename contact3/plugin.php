<?php
/**
 *  Contact3 - Contact form plugin for Bludit version 3
 *
 *  @package    Bludit
 *  @subpackage Contact3
 *  @category   Plugins
 *  @author     novafacile OÜ
 *  @copyright  2021 by novafacile OÜ
 *  @license    MIT
 *  @version    2.1.1
 *  @see        https://github.com/novafacile/bludit-plugins
 *  @release    2021-02-27
 *  @notes      idea based on https://github.com/Fred89/bludit-plugins/tree/master/contact
 *  This program is distributed in the hope that it will be useful - WITHOUT ANY WARRANTY.
 *
 *  used packages: PHPMailer https://github.com/PHPMailer/PHPMailer/
 */

class pluginContact3 extends Plugin {

  private $senderEmail = '';
  private $senderName = '';
  private $message = '';
  private $success = false;
  private $errorMessage = '';

  // install plugin
  public function init() {
    $this->dbFields = array(
      'email' => '',
      'name' => '',
      'page' => '',
      'type' => 'text',
      'subject' => '',
      'user-cc' => false,
      'user-cc-subject' => '',
      'smtphost' => '',
      'smtpport' => '',
      'smtpencryption' => '',
      'username' => '',
      'password' => '',
      'sendEmailFrom' => 'fromUser',
      'domainAddress' => '',
      'gdpr-checkbox' => false,
      'gdpr-checkbox-text' => '',
      'gdpr-text-in-email' => false,
      'spam-protection' => '',
      'recaptcha-site-key' => '',
      'recaptcha-secret-key' => '',
      'hcaptcha-site-key' => '',
      'hcaptcha-secret-key' => '',
      'logical-question-0' => '',
      'logical-answer-0' => ''
    );
  }

  // config form
  public function form() {
    global $L, $staticPages;

    /*** form start ***/
    $html = "\n";
    $html .= '<style type="text/css">
            .plugin-form .contact3-form label {margin-top: 0 !important; }
            .contact3-form .short-input { max-width: 200px };
            </style>';

    /*** tab navi ***/
    $html .= '<div class="tab-content contact3-form" id="nav-tabContent">';
    $html .= '<nav class="mb-3">
      <div class="nav nav-tabs" id="nav-tab" role="tablist">
        <a class="nav-item nav-link active" id="nav-contact3-general-tab" data-toggle="tab" href="#contact3-general" role="tab" aria-controls="nav-contact3-general" aria-selected="true">'.$L->get('General').'</a>
        <a class="nav-item nav-link" id="nav-contact3-smtp-tab" data-toggle="tab" href="#contact3-smtp" role="tab" aria-controls="nav-contact3-advanced" aria-selected="false">'.$L->get('SMTP').'</a>
        <a class="nav-item nav-link" id="nav-contact3-gpdr-tab" data-toggle="tab" href="#contact3-gpdr" role="tab" aria-controls="nav-contact3-gpdr" aria-selected="false">'.$L->get('GDPR').'</a>
        <a class="nav-item nav-link" id="nav-contact3-spam-tab" data-toggle="tab" href="#contact3-spam" role="tab" aria-controls="nav-contact3-spam" aria-selected="false">'.$L->get('Spam Protection').'</a>
      </div>
    </nav>
    ';

    /*** basics ***/
    $html .= '<div class="tab-pane fade show active" id="contact3-general" role="tabpanel" aria-labelledby="contact3-general-tab">';
    $html .= Bootstrap::formTitle(array('title' => $L->get('General')));

    // select page where Contact3 is shown
    $options = array('' => '');
    try { // true to get page name of saved page
      $contact3Page = $this->getValue('page');
      if($contact3Page){
        $page = new Page($contact3Page);
        $options[$contact3Page] = $page->title();
      }
    } catch (Exception $e){
      // continue
    }

    $html .= Bootstrap::formSelect(array(
      'name' => 'page',
      'label' => $L->get('Contact3 Page'),
      'options' => $options,
      'selected' => $this->getValue('page'),
      'tip'=> $L->get('Only published content appears in this list. Remove the selected page to deactivate the contact form.')
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

    // email receiver
    $html .= Bootstrap::formInputText(array(
              'name' => 'email',
              'label' => $L->get('Your Email'),
              'value' => $this->getValue('email'),
              'tip' => $L->get('This is the address you want the email to be sent TO.')
              ));

    // email receiver name
    $html .= Bootstrap::formInputText(array(
              'name' => 'name',
              'label' => $L->get('Email Name'),
              'value' => $this->getValue('name'),
              'tip' => $L->get('This is used for email receiver name and in CC email for sender name.')
              ));

    // email subject
    $html .= Bootstrap::formInputText(array(
              'name' => 'subject',
              'label' => $L->get('Email Subject'),
              'value' => $this->getValue('subject')
              ));

    // email type
    $html .= Bootstrap::formSelect(array(
              'name' => 'type',
              'label' => $L->get('Content Type'),
              'options' => array(
                'text' => $L->get('Text format'),
                'html' => $L->get('HTML format')
              ),
              'selected' => $this->getValue('type'),
              'class' => 'mb-4 short-input'
              ));


    // sender email settings
    $html .= Bootstrap::formTitle(array('title' => $L->get('Contact3 Email Settings')));

    // send from which address
    $html .= Bootstrap::formSelect(array(
              'name' => 'sendEmailFrom',
              'label' => $L->get('Email sender address'),
              'options' => array(
                'fromUser' => $L->get('Email address from visitor'),
                'fromTo' => $L->get('Recipients email address (above)'),
                'fromDomain' => $L->get('Special email sender address (below)')
              ),
              'selected' => $this->getValue('sendEmailFrom'),
              'tip' => $L->get('If possible, use the email address of the visitor. However, some hosts require an email address known on the server. In this case, you can choose between the recipients address (above) or a special sender email address (below) as the sender.')
              ));

    // from domain email address
    $html .= Bootstrap::formInputText(array(
              'name' => 'domainAddress',
              'label' => $L->get('Special Email Sender Address'),
              'value' => $this->getValue('domainAddress'),
              'tip' => $L->get('Only required if you use a special email sender address. Some hosts only allow sending emails from a locally known address.')
              ));

    // send user an the email to cc settings
    $html .= Bootstrap::formTitle(array('title' => $L->get('Send user a copy')));
    $html .= '<p>'.$L->get('The visitor receives a copy of his message.').'</p>';

    // cc mail to sender
    $html .= Bootstrap::formSelect(array(
              'name' => 'user-cc',
              'label' => $L->get('Send user a copy'),
              'class' => 'short-input',
              'options' => array(
                false => $L->get('deactivate'),
                true => $L->get('activate'),
              ),
              'selected' => $this->getValue('user-cc')
              ));

    // cc mail subject
    $html .= Bootstrap::formInputText(array(
              'name' => 'user-cc-subject',
              'label' => $L->get('Subject for Copy'),
              'value' => $this->getValue('user-cc-subject'),
              'tip' => $L->get('You can set an individual email subject text for the email copy. If emtpy the general subject is used.')
              ));

    // close general tab
    $html .= '</div>';


    /*** smtp settings ***/
    $html .= '<div class="tab-pane fade" id="contact3-smtp" role="tabpanel" aria-labelledby="contact3-smtp-tab">';
    $html .= Bootstrap::formTitle(array('title' => $L->get('SMTP')));
    $html .= '<p>'.$L->get('Please fill in the following values if you want to send messages by a smtp account.').'</p>';

    // smtp host
    $html .= Bootstrap::formInputText(array(
              'name' => 'smtphost',
              'label' => $L->get('SMTP Host'),
              'value' => $this->getValue('smtphost')
              ));

    // smtp port
    $html .= Bootstrap::formInputText(array(
              'name' => 'smtpport',
              'label' => $L->get('SMTP Port'),
              'class' => 'short-input',
              'value' => $this->getValue('smtpport')
              ));

    // smtp encryption
    $html .= Bootstrap::formSelect(array(
              'name' => 'smtpencryption',
              'label' => $L->get('SMTP Encryption'),
              'class' => 'short-input',
              'options' => array(
                false => $L->get('deactivate'),
                'starttls' => $L->get('STARTTLS'),
                'smtps' => $L->get('SSL/TLS')
              ),
              'selected' => $this->getValue('smtpencryption')
              ));

    // smtp username
    $html .= Bootstrap::formInputText(array(
              'name' => 'username',
              'label' => $L->get('SMTP Username'),
              'value' => $this->getValue('username')
              ));

    // smtp password
    $html .= Bootstrap::formInputText(array(
              'name' => 'password',
              'type' => 'password',
              'label' => $L->get('SMTP Password'),
              'value' => $this->getValue('password')
              ));

    // close smtp tab
    $html .= '</div>';

    /*** gpdr settings ***/
    $html .= '<div class="tab-pane fade" id="contact3-gpdr" role="tabpanel" aria-labelledby="contact3-gpdr-tab">';
    $html .= Bootstrap::formTitle(array('title' => $L->get('GDPR')));
    $html .= '<p>'.$L->get('If necessary, a checkbox with a custom legal text can be added to the form. The visitor must activate the checkbox to submit.').'</p>';

    // activate or deactivate gpdr checkbox
    $html .= Bootstrap::formSelect(array(
              'name' => 'gdpr-checkbox',
              'label' => $L->get('GDPR Checkbox'),
              'class' => 'short-input',
              'options' => array(
                false => $L->get('deactivate'),
                true => $L->get('activate'),
              ),
              'selected' => $this->getValue('gdpr-checkbox')
              ));

    $html .= Bootstrap::formTextarea(array(
              'name' => 'gdpr-checkbox-text',
              'rows' => 4,
              'label' => $L->get('GDPR Checkbox Legal Text'),
              'value' => $this->getValue('gdpr-checkbox-text'),
              'placeholder' => '',
              'tip' => $L->get('HTML is allowed')
              ));

    // show gpdr text in email
    $html .= Bootstrap::formSelect(array(
              'name' => 'gdpr-text-in-email',
              'label' => $L->get('GDPR Text in Email'),
              'class' => 'short-input',
              'options' => array(
                false => $L->get('deactivate'),
                true => $L->get('activate'),
              ),
              'selected' => $this->getValue('gdpr-text-in-email'),
              'tip' => $L->get('If enabled, the GDPR legal text will be inserted into the email.')
              ));


    // close dpdr tab
    $html .= '</div>';

    /*** spam protection ***/
    $html .= '<div class="tab-pane fade" id="contact3-spam" role="tabpanel" aria-labelledby="contact3-spam-tab">';
    $html .= Bootstrap::formTitle(array('title' => $L->get('Spam Protection')));
    $html .= '<p>'.$L->get('You can use a captcha service for spam protection. This adds a captcha in the end of the form.').'</p>';

    // activate or deactivate spam protection
    $html .= Bootstrap::formSelect(array(
              'name' => 'spam-protection',
              'label' => $L->get('Spam Protection'),
              'options' => array(
                '' => $L->get('deactivate'),
                'reCaptcha' => $L->get('Google reCaptcha'),
                'hCaptcha' => $L->get('hCaptcha'),
                'logical-question' => $L->get('Logical Question')
              ),
              'selected' => $this->getValue('spam-protection'),
              'class' => 'mb-4 short-input'
              ));

    // Google reCaptcha
    $html .= Bootstrap::formTitle(array('title' => $L->get('Google reCaptcha')));
    $html .= '<p>'.$L->get('Before activation, you need to register your website at <a href="https://www.google.com/recaptcha" target="_blank">Google reCaptcha</a>. Choose Version 2. The <i>Site Key</i> and <i>Secret Key</i> are required.').'</p>';

    // reCaptcha website key
    $html .= Bootstrap::formInputText(array(
              'name' => 'recaptcha-site-key',
              'label' => $L->get('Website Key'),
              'value' => $this->getValue('recaptcha-site-key')
              ));

    // reCaptcha website secret
    $html .= Bootstrap::formInputText(array(
              'name' => 'recaptcha-secret-key',
              'type' => 'password',
              'label' => $L->get('Secret Key'),
              'value' => $this->getValue('recaptcha-secret-key'),
              'class' => 'mb-4'
              ));

    // hCaptcha
    $html .= Bootstrap::formTitle(array('title' => $L->get('hCaptcha')));
    $html .= '<p>'.$L->get('Before activation, you need to register your website at <a href="https://www.hcaptcha.com/" target="_blank">hCaptcha</a>. The <i>Site Key</i> and <i>Secret Key</i> are required.').'</p>';

    // hCaptcha website key
    $html .= Bootstrap::formInputText(array(
              'name' => 'hcaptcha-site-key',
              'label' => $L->get('Website Key'),
              'value' => $this->getValue('hcaptcha-site-key')
              ));

    // hCaptcha website secret
    $html .= Bootstrap::formInputText(array(
              'name' => 'hcaptcha-secret-key',
              'type' => 'password',
              'label' => $L->get('Secret Key'),
              'value' => $this->getValue('hcaptcha-secret-key')
              ));

    // Logical Question
    $html .= Bootstrap::formTitle(array('title' => $L->get('Logical Question')));
    $html .= '<p>'.$L->get('Use a logical question for simple spam protection.').'</p>';

    // Logical Question Value
    $html .= Bootstrap::formInputText(array(
              'name' => 'logical-question-0',
              'label' => $L->get('Question'),
              'value' => $this->getValue('logical-question-0'),
              'tip' => $L->get('Example: &quot;Please enter the second word of the following sentence: My sister loves cookies.&quot;')
              ));

    $html .= Bootstrap::formInputText(array(
              'name' => 'logical-answer-0',
              'label' => $L->get('Answer'),
              'value' => $this->getValue('logical-answer-0'),
              'tip' => $L->get('To minimize erroneous entries, the response is always checked case-insensitively.')
              ));

    // close spam protection tab
    $html .= '</div>';

    /*** form end ***/
    $html .= '</div>';
    return $html;
  }

  // send email and redirect to page with contact form
  public function beforeAll(){
    if(!$this->webhook($this->webhookUrl())){
      return;
    }

    // start session for transfer result after sending mail (looses vars in script after success because of self redirect)
    $this->session();

    if(isset($_POST['submit'])) {
      global $L;

      $this->readPost();

      $errors = $this->validate();
      if(!empty($errors)){
        foreach ($errors as $value) {
          $this->errorMessage .= $value.'<br>';
        }
      }

      $validateCaptcha = $this->captchaValidation();
      if($validateCaptcha !== true){
        $this->errorMessage .= $L->get($validateCaptcha).'<br>';
      }

      // stop if error
      if($this->errorMessage){
        return; 
      }

      // send email
      require __DIR__ . DS . 'phpmailer' . DS . 'PHPMailerAutoload.php';
      $receiverMail = $this->getValue('email');
      $receiverName = $this->getValue('name');
      switch ($this->getValue('sendEmailFrom')){ // Set email FROM address
        case "fromTo":
          $senderEmail = $this->getValue('email');
          $senderName = $this->getValue('name');
          break;
        case "fromDomain":
          $senderEmail = $this->getValue('domainAddress');
          $senderName = $this->getValue('name');
          break;    
        default: // fromUser
          $senderEmail = $this->senderEmail;
          $senderName = $this->senderName;
      }
      $replayToEmail = $this->senderEmail;
      $replayToName = $this->senderName;

      $success = $this->sendEmail($receiverMail, $receiverName, $this->getSubject(), $senderEmail, $senderName, $replayToEmail, $replayToName);

      // send cc if set
      if($success && $this->getValue('user-cc')){
        $receiverMail = $this->senderEmail;
        $receiverName = $this->senderName;
        $replayToEmail = $this->getValue('email');
        $replayToName = $this->getValue('name');
        $successCC = $this->sendEmail($receiverMail, $receiverName, $this->getSubject('CC'), $senderEmail, $senderName, $replayToEmail, $replayToName);
      }

      if($success){
        $_SESSION['contact3']['success'] = true;
        header('Location: '. $this->webhookUrl()); // redirect to himself to prevent resending on page reload
        exit;
      } else {
        $this->errorMessage .= $L->get('an-error-occurred-while-sending');
      }
    }
  }

  // Load CSS for contact form
  public function siteHead() {
    if($this->webhook($this->webhookUrl())) {
      $html = '';
      $css = THEME_DIR_CSS . 'contact3.css';
      if(file_exists($css)) {
        $html .= Theme::css('css' . DS . 'contact3.css');
      } else {
        $html .= '<link rel="stylesheet" href="' .$this->htmlPath(). 'layout' . DS . 'contact3.css">' .PHP_EOL;
      }

      switch ($this->getValue('spam-protection')) {
        case 'reCaptcha':
          $html .= '<script src="https://www.google.com/recaptcha/api.js" async defer></script>';
          break;
        case 'hCaptcha':
          $html .= '<script src="https://hcaptcha.com/1/api.js" async defer></script>';
          break;      
        default:
          // do nothing
          break;
      }

      return $html;
    }
  } 


  // Load contact form
  public function pageEnd(){
    if(!$this->webhook($this->webhookUrl())){
      return;
    }

    $this->session(); // start session to check of sending mail was successful
    if(isset($_SESSION['contact3']['success'])){
      $this->success = true;
      array_pop($_SESSION['contact3']);
    }

    // include contact form
    $this->includeContactForm();
    
  }

  /*********
   * template methods
   *********/

  public function frontendMessage(){
    global $L;
    if($this->success) {
      $html = '<div class="alert alert-success">' .$L->get('thank-you-for-contact'). '</div>' ."\r\n";
    } elseif($this->errorMessage) {
      $html = '<div class="alert alert-danger">'. rtrim($this->errorMessage, '<br>'). '</div>' ."\r\n";
    } else {
      $html = '';
    }
    return $html;
  }


  public function captchaForm($inputClass = ''){
    global $L;
    switch ($this->getValue('spam-protection')) {
      case 'reCaptcha':
        return '<div class="g-recaptcha" data-sitekey="'.$this->getValue('recaptcha-site-key').'"></div>';
        break;
      case 'hCaptcha':
        return '<div class="h-captcha" data-sitekey="'.$this->getValue('hcaptcha-site-key').'"></div>';
        break;
      case 'logical-question':
        return '<div class="contact3-lq">'.$this->getValue('logical-question-0').'</div>
              <input type="text" name="contact3-lqa" class="'.$inputClass.'" placeholder="'.$L->get('Your Answer').'" autocomplete="off" required>';
        break;
    }
  }

  // this method exists only for foreign template compatibility
  public function googleRecaptchaForm(){
    if($this->getValue('google-recaptcha')){
      return $html = '<div class="g-recaptcha" data-sitekey="'.$this->getValue('recaptcha-site-key').'"></div>';
    } else {
      return;
    }
  }


  /****
   * private methods
   *****/
  
  private function webhookUrl(){
    global $site;
    $pagePrefix = $site->getField('uriPage');
    $pagePrefix = ltrim($pagePrefix, '/');
    return $webhook = $pagePrefix.$this->getValue('page');
  }

  private function session(){
    $session_id = session_id();
    if(!$session_id){
      $session_id = session_start();
    }
    return $session_id;
  }

  private function sendEmail($receiverMail, $receiverName, $subject, $senderEmail, $senderName = '', $replayToEmail = false, $replayToName = ''){
    try {
      $mail = new PHPMailer();
      
      if($this->getValue('smtphost')) {
        $mail->isSMTP();
        $mail->Host = $this->getValue('smtphost');
        $mail->Port = $this->getValue('smtpport');
        switch ($this->getValue('smtpencryption')) {
          case 'starttls':
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
          break;
          case 'smtps':
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            break;
        }
         
        $mail->SMTPAuth = true;
        $mail->Username = $this->getValue('username');
        $mail->Password = html_entity_decode($this->getValue('password')); // Function is needed if password contains special characters like '&'
      }

      $mail->setFrom($senderEmail, $senderName);
      $mail->addAddress($receiverMail, $receiverName);
      if($replayToEmail){
        $mail->addReplyTo($replayToEmail, $replayToName);
      }

      $mail->CharSet = CHARSET;
      $mail->Subject = $subject;
      $mail->isHTML($this->isHTML());
      $mail->Body = $this->getEmailText();

      return $mail->send();

    } catch (Exception $e){
      $return = false;
    }
  }

  private function isHtml(){
    if($this->getValue('type') === 'html') {
      return true;
    } else {
      return false;
    }
  }

  private function readPost(){
    if(isset($_POST['name'])) { 
      $this->senderName = trim(strip_tags($_POST['name']));
    }
    if(isset($_POST['email'])) {
      $this->senderEmail = trim(preg_replace("/[^0-9a-zA-ZäöüÄÖÜÈèÉéÂâáÁàÀíÍìÌâÂ@ \-\+\_\.]/", " ", $_POST['email']));
    }
    if(isset($_POST['message'])){
      $this->message = trim(strip_tags($_POST['message']));
    }
  }

  private function validate(){
    global $L;
    $errors = [];

    if(trim($this->senderName)==='')
      array_push($errors, $L->get('Please enter your name'));

    if(trim($this->senderEmail)==='' || !filter_var($this->senderEmail, FILTER_VALIDATE_EMAIL))
      array_push($errors, $L->get('Please enter a valid email address'));

    if(trim($this->message)==='')
      array_push($errors, $L->get('Please enter the content of your message'));
    
    if ($this->getValue('gdpr-checkbox') && !isset($_POST['gdpr-checkbox']))
      array_push($errors, $L->get('Please accept the privacy policy'));
    
    return $errors;
  }


  private function getSubject($type = false){
    global $site, $L;

    switch ($type) {
      case 'CC':
        $subject = $this->getValue('user-cc-subject');
        if(empty($subject)){
          $subject = $this->getValue('subject'); // use default subject if no CC subject is defined
        }
        break;  
      default:
        $subject = $this->getValue('subject');
        break;
    }
    
    if(empty($subject)){
      $subject = $L->get('New contact from'). ' - ' .$site->title();
    }

    return $subject;
  }


  private function getEmailText(){
    global $L;
    if($this->isHtml()) {
      $emailText  = '<b>'.$L->get('Your Name').': </b>'.$this->senderName.'<br>';
      $emailText .= '<b>'.$L->get('Your Email').': </b>'.$this->senderEmail.'<br>';
      $emailText .= '<b>'.$L->get('Your Message').': </b><br>'.nl2br($this->message).'<br><br>';
      if($this->getValue('gdpr-checkbox') && $this->getValue('gdpr-text-in-email')){
        $emailText .= sanitize::htmlDecode($this->getValue('gdpr-checkbox-text')).'<br>';
      }
    } else {
      $emailText  = $L->get('Your Name').': '.$this->senderName."\r\n\r";
      $emailText .= $L->get('Your Email').': '.$this->senderEmail."\r\n\r";
      $emailText .= $L->get('Your Message').': '."\r\n".$this->message."\r\n\r\n";
      if($this->getValue('gdpr-checkbox') && $this->getValue('gdpr-text-in-email')){
        $emailText .= strip_tags(sanitize::htmlDecode($this->getValue('gdpr-checkbox-text')))."\r\n\r";
      }
    }
    return $emailText;
  }


  private function includeContactForm(){
    global $page, $security, $L;
    $template = THEME_DIR_PHP . 'contact3.php';
    if(file_exists($template)) {
      include($template);
    } else {
      include(__DIR__ . DS . 'layout' . DS . 'contact3.php');
    }   
  }

  private function captchaValidation(){
    if(!$this->getValue('spam-protection')){
      return true;
    }

    switch ($this->getValue('spam-protection')) {
      case 'reCaptcha':
        $secretKey = $this->getValue('recaptcha-secret-key');
        $json = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$secretKey.'&response='.$_POST['g-recaptcha-response']);
        $data = json_decode($json);
        if($data->success === true){
          return true;
        } else {
          return 'Please confirm that you are not a robot.';
        }
        break;
      
      case 'hCaptcha':
        $data = array(
          'secret' => $this->getValue('hcaptcha-secret-key'),
          'response' => $_POST['h-captcha-response']
        );
        $verify = curl_init();
        curl_setopt($verify, CURLOPT_URL, "https://hcaptcha.com/siteverify");
        curl_setopt($verify, CURLOPT_POST, true);
        curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($verify);
        $data = json_decode($response);
        if($data->success === true){
          return true;
        } else {
          return 'Please confirm that you are a human.';
        }
        break;

      case 'logical-question':
        if(isset($_POST['contact3-lqa'])){
          $answer = trim(ucwords($this->getValue('logical-answer-0')));
          $input = trim(ucwords($_POST['contact3-lqa']));
          if($answer === $input) { return true; }
        }
        return 'Please correct your answer so that we recognize you as a human being.';
        break;

      default:
        return 'Sorry, there seems to be an error in validating if you are a human.';
    }
  }

}
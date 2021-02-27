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
 */
?>
<form method="post" action="<?php echo '.' . DS . $page->slug(); ?>" class="contact3">
  <?php echo $this->frontendMessage(); ?>
  <input type="hidden" name="tokenCSRF" value="<?php echo $security->getTokenCSRF(); ?>">
  
  <div class="form-group">
     <input id="name" type="text" name="name" value="<?php echo sanitize::html($this->senderName); ?>" placeholder="<?php echo $L->get('Your Name'); ?>" class="form-control" required>
  </div>

  <div class="form-group">
     <input id="email" type="email" name="email" value="<?php echo sanitize::email($this->senderEmail); ?>" placeholder="<?php echo $L->get('Your Email'); ?>" class="form-control" required>
  </div>

  <div class="form-group">
     <textarea id="message" rows="6" name="message" placeholder="<?php echo $L->get('Your Message'); ?>" class="form-control" required><?php echo sanitize::html($this->message); ?></textarea>
  </div>

  <?php if ($this->getValue('gdpr-checkbox')): ?>
    <div class="form-check">
      <input type="checkbox" name="gdpr-checkbox" id="gdpr-checkbox" class="form-check-input" <?php if(isset($_POST['gdpr-checkbox'])) echo 'checked'; ?> required>
      <label for="gdpr-checkbox" class="form-check-label"><?php echo sanitize::htmlDecode($this->getValue('gdpr-checkbox-text')); ?></label>
    </div>
  <?php endif; ?>   

  <div class="form-group">
      <?php echo $this->captchaForm('form-control contact3-lqa'); // parameter is/are optional class(es) for logical question answer text input ?>   
  </div>


  <button id="submit" name="submit" type="submit" class="btn btn-primary"><?php echo $L->get('Send'); ?></button>
</form>
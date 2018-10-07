<?php 
/**
 *  Contact layout
 *
 *  @package Bludit
 *  @subpackage Contact
 *  @author Frédéric K
 *	@author novafacile OÜ.
 *  @info: Duplicate this layout in your themes/YOUR_THEME/php/ 
 *	for a custom template.
 */	
?>
<form method="post" action="<?php echo '.' . DS . $page->slug(); ?>" class="contact3">
	<?php echo $this->frontendMessage(); ?>
	<input type="hidden" name="tokenCSRF" value="<?php echo $security->getTokenCSRF(); ?>">
	
	<div class="form-group">
	   <input id="name" type="text" name="name" value="<?php echo sanitize::html($this->senderName); ?>" placeholder="<?php echo $L->get('Name'); ?>" class="form-control" >
	</div>

	<div class="form-group">
	   <input id="email" type="email" name="email" value="<?php echo sanitize::email($this->senderEmail); ?>" placeholder="<?php echo $L->get('Email'); ?>" class="form-control">
	</div>

	<div class="form-group">
	   <textarea id="message" rows="6" name="message" placeholder="<?php echo $L->get('Message'); ?>" class="form-control"><?php echo sanitize::html($this->message); ?></textarea>
	</div>

	<input type="checkbox" name="interested">
	<button id="submit" name="submit" type="submit" class="btn btn-primary"><?php echo $L->get('Send'); ?></button>
</form>
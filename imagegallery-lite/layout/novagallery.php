<?php 
/**
 *  ImageGallery Lite for Bludit
 *
 *  @package    Bludit
 *  @subpackage ImageGallery Lite
 *  @category   Plugins
 *  @author     novafacile OÜ
 *  @copyright  2021 by novafacile OÜ
 *  @license    AGPL-3.0
 *  @version    1.1.0
 *  @see        https://github.com/novafacile/bludit-plugins
 *  @release    2021-06-18
 *  @notes      based on PHP Image Gallery novaGallery - https://novagallery.org
 *  This program is distributed in the hope that it will be useful - WITHOUT ANY WARRANTY.
 */
?>
<?php if($this->getValue('gallery-title')): ?>
  <h3><?php echo $this->getValue('gallery-title'); ?></h3>
<?php endif; ?>

  <div class="novagallery">
    <?php foreach ($images as $image => $timestamp): ?>
    <div class="novagallery-image">
      <a href="<?php echo $pathLarge.$image; ?>" class="novagallery-image-link">
        <img src="<?php echo $pathThumbnail.$image; ?>">
      </a>
    </div>
    <?php endforeach; ?>
  </div>






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
 *  @see        https://github.com/novafacile/bludit-plugins
  *  This program is distributed in the hope that it will be useful - WITHOUT ANY WARRANTY.
 */
?>
<?php if($this->getValue('gallery-title')): ?>
  <h3><?php echo $this->getValue('gallery-title'); ?></h3>
<?php endif; ?>
  <div class="imagegallery">
    <?php foreach ($images as $image): ?>
    <div class="imagegallery-image">
      <a href="<?= $image['large'] ?>" class="imagegallery-image-link">
        <img src="<?= $image['thumbnail'] ?>">
      </a>
    </div>
    <?php endforeach; ?>
  </div>
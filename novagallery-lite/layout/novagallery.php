<?php 
/**
 *  novaGallery Lite for Bludit - simple image gallery for Bludit 3
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
 */
?>
<?php if($this->getValue('gallery-title')): ?>
  <h3><?php echo $this->getValue('gallery-title'); ?></h3>
<?php endif; ?>

  <div class="row w-100 mt-3">
    <?php foreach ($images as $image => $timestamp): ?>
    <div class="col-12 col-sm-6 col-md-4 col-xl-3 text-center mb-3 novagallery">
      <a href="<?php echo $pathLarge.$image; ?>" class="image">
        <img src="<?php echo $pathThumbnail.$image; ?>" style="max-width: 100%;max-height:400px;">
      </a>
    </div>
    <?php endforeach; ?>
  </div>






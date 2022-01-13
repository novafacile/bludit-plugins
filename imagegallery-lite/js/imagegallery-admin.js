/**
 * AdminJS for Bludit Image Gallery Lite
 * @author    novafacile OÜ
 * @copyright 2022 by novafacile OÜ
 * @license   AGPL-3.0
 * @see       https://bludit-plugins.com
 * This program is distributed in the hope that it will be useful - WITHOUT ANY WARRANTY.
 */
$(function(){
  var lightbox = new SimpleLightbox(".imagegallery-images .image", {});
  selectPage('#jspage');
  $('.imagegallery-del-file').bind("click", function(){deleteImage(this);});
});

function selectPage(selector){
  $(selector).select2({
    allowClear: true,
    theme: "bootstrap4",
    placeholder: imageGallery.L.startTypingPlaceholder,
    minimumInputLength: 2,
    ajax: {
      url: imageGallery.config.ajaxPagesUrl,
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
  });
}

function deleteImage(el){
  $.post(imageGallery.config.ajaxUrl, { 
    tokenCSRF: $('#jstokenCSRF').val(),
    action: 'deleteImage',
    album: $(el).data("album"),
    file: $(el).data("file")
  }, 
  function(){
    let selector = '#imagegallery-image-' + $(el).data("number");
    $(selector).hide();
  }).fail(function(){
    $.alert({
      title: imageGallery.L.error,
      content: imageGallery.L.deleteImageError
    });
  });
}
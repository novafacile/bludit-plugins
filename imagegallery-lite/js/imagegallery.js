
$(function(){
  var lightbox = new SimpleLightbox(".imagegallery-images .image", {});

  $('.imagegallery-del-file').bind("click", function(){
    let url = $(this).data("url") + 'ajax/delete-file.php';
    let album = $(this).data("album");
    let file = $(this).data("file");
    let number = $(this).data("number");
    let tokenCSRF = $('#jstokenCSRF').val();

    $.post(url, {
      'album': album,
      'file': file,
      'tokenCSRF': tokenCSRF
    }, function(){
      let selector = '#imagegallery-image-' + number;
      $(selector).hide();
    }).fail(function(){
      alert('Error. Could not delete file.');
    });

  });

});
<?php
/**
 * Image Gallery Lite - Image Gallery for Bludit3
 * Helper object
 * 
 * @author     novafacile OÜ
 * @copyright  2022 by novafacile OÜ
 * @license    AGPL-3.0
 * @see        https://bludit-plugins.com
 * @notes      based on PHP Image Gallery novaGallery - https://novagallery.org
 * This program is distributed in the hope that it will be useful - WITHOUT ANY WARRANTY.
 */
namespace novafacile;

class BluditImageGalleryHelper {
  
  public function adminJSData($domainPath) {
    global $L;
    return '<script>
              var imageGallery = {
                config: {
                  ajaxUrl: "'.$domainPath.'ajax/request.php",
                  ajaxPagesUrl: "'.HTML_PATH_ADMIN_ROOT.'ajax/get-published"
                },
                L: {
                  startTypingPlaceholder: "'.$L->get('Start typing to see a list of suggestions.').'",
                  deleteImageError: "'.$L->get('Error: Image could not be deleted.').'"
                }
              };
          </script>
          ';
  }

  public function dropzoneJSData($album){
    global $security, $L;
    return '<script>
              Dropzone.options.imagegalleryUpload = {
                url: imageGallery.config.ajaxUrl,
                params: {
                  tokenCSRF: "'.$security->getTokenCSRF().'",
                  action: "uploadImage",
                  album: "'.$album.'"
                },
                addRemoveLinks: true,
                acceptedFiles: ".jpg,.jpeg,.png",
                dictDefaultMessage: "<b>'.$L->get('Drop files here or click to upload.').'</b><br><br>('.$L->get('Upload will start immediately.').').",
                dictFileTooBig: "'.$L->get('File is to big. Max. file size:').' {{maxFilesize}} MiB",
                dictInvalidFileType: "'.$L->get('This is not a JPEG or PNG.').'",
                dictResponseError: "{{statusCode}} '.$L->get('Server error during upload.').'",
                dictCancelUpload: "'.$L->get('Cancel upload').'",
                dictUploadCanceled: "'.$L->get('Upload canceled').'",
                dictCancelUploadConfirmation: "'.$L->get('Cancel upload?').'",
                dictRemoveFile: "'.$L->get('Remove').'",
                init: function(){
                  this.on("queuecomplete", function() { $("#imagegallery-reload-button").removeClass("d-none"); });
                  this.on("addedfile", function(file) { $("#imagegallery-reload-button").addClass("d-none"); });
                }
              };
            </script>
            ';
  }
}
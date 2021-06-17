<?php defined('BLUDIT') or die('Bludit CMS.');

/**
 * AJAX Helper for novafacile Bludit Plugins
 * @author novafacile OÜ
 * @copyright 2021 by novafacile OÜ
 * @license MIT
 * @see https://github.com/novafacile/bludit-plugins
 *  This program is distributed in the hope that it will be useful - WITHOUT ANY WARRANTY.
 */

class AJAX {

  static function setHeader(){
    header('Content-Type: application/json');
  }
  
  static function auth(){
    self::checkSession();
    self::checkRole();
    self::checkCSRF();
  }

  private static function checkSession(){
    session_name('BLUDIT-KEY');
    session_start();
    if(!isset($_SESSION['s_role'])){
      self::exit(401);
    }
  }

  private static function checkRole(){
    $role = $_SESSION['s_role'];
    if($role !== 'admin' && $role != 'author' && $role != 'editor'){
      self::exit(401);
    }
  }

  public static function checkCSRF(){
    if(!isset($_SESSION['s_tokenCSRF']) || !isset($_POST['tokenCSRF'])){
      self::exit(401);
    }
    if($_SESSION['s_tokenCSRF'] != $_POST['tokenCSRF']){
     self::exit(401); 
    }
  }

  public static function exit($statusCode = 403, $message = false){
    switch ($statusCode) {
      case 200:
        $header = 'HTTP/1.1 200 Found';
        $defaultMessage = 'Success';
        break;
      case 400:
        $header = 'HTTP/1.1 400 Bad Request';
        $defaultMessage = '400 Bad Request';
        break;
      case 401:
        $header = 'HTTP/1.1 401 Unauthorized';
        $defaultMessage = '401 Unauthorized';
        break;
      case 404:
        $header = 'HTTP/1.1 404 Not Found';
        $defaultMessage = '404 Not Found';
        break;
      case 500:
        $header = 'HTTP/1.1 500 Internal Server Error';
        $defaultMessage = 'Internal Server Error';
        break;
      default:
        $header = 'HTTP/1.1 403 Forbidden';
        $defaultMessage = '403 Forbidden';
        break;
    }

    if(!$message){
      $message = $defaultMessage;
    }

    header($header);
    echo json_encode($message);
    exit;
  }
  
}

?>
<?php defined('BLUDIT') or die('Bludit CMS.');
/**
 * Config helper for novafacile Bludit Plugins
 * @author    novafacile OÜ
 * @copyright 2022 by novafacile OÜ
 * @license   MIT
 * @see       https://bludit-plugins.com
 * This program is distributed in the hope that it will be useful - WITHOUT ANY WARRANTY.
 */

class Config {

  private $file;
  private $config = [];
  
  function __construct($file, $firstLine = false) {  
    $this->file = $file;

    if (file_exists($file)) {
      // Read JSON file
      $lines = file($file);

      // Remove the first line, the first line is for security reasons
      if ($firstLine) {
        unset($lines[0]);
      }

      // Regenerate the JSON file
      $json = implode($lines);

      // Unserialize, JSON to Array
      $this->config = json_decode($json, true);
    }
  }

  public function getField($key) {
    if(isset($this->config[$key])) {
      return $this->config[$key];
    } else {
      return null;
    }
  }

  public function setField($key, $value){
    $this->config[$key] = $value;
  }

  public function deleteField($key){
    unset($this->config[$key]);
  }

  public function save(){
    $data = json_encode($this->config);

    if (file_put_contents($this->file, $data, LOCK_EX)) { // LOCK_EX flag to prevent anyone else writing to the file at the same time.
      return true;
    } else {
      Log::set(__METHOD__.LOG_SEP.'Error occurred when trying to save the database file.', LOG_TYPE_ERROR);
      return false;
    }
  }

}


?>
<?php

namespace Papyrillio\PapPalBundle\Service;

class ImageUntiffer{
  public static function untiffDirectory($directory){
    foreach(scandir($directory) as $file){
      if(preg_match('/^[^\.]/', $file)){
        $fullpath = $directory . '/' . $file;
        if(is_dir($fullpath)){
          self::untiffDirectory($fullpath);
        } else if(is_file($fullpath) && preg_match('/\.tiff?$/', $fullpath)){
          self::untiffFile($fullpath);
        }
      }
    }
  }

  public static function untiffFile($tiffFile){
    $jpgFile = preg_replace('/\.tiff$/', '.jpg', $tiffFile);
    if(!file_exists($jpgFile)){
      //$command = '/usr/bin/sips -s format jpeg ' . $tiffFile . ' --out ' . $jpgFile;
      $command = '/opt/local/bin/convert ' . $tiffFile . ' ' . $jpgFile;
      $output  = array();
      $return  = 0;
      $execReturn = exec($command, $output, $return);

      //echo "\n[" . $command . "]\n[" . implode("\n", $output) . "]\n[" . $return . "]\n[" . $execReturn . "]";
      unlink($tiffFile);
    }
  }
}

?>

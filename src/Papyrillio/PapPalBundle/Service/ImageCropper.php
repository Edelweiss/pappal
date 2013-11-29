<?php

namespace Papyrillio\PapPalBundle\Service;

use Exception;

class ImageCropper extends ImagePeer{
  public $imageCounter = 0;
  public $cropCounter = 0;
  public $targetDirectory = null; // thumbnail/HGV_1000/HGV_ID
  public $prefix = null; // usually HGV id

  protected function configure($path, $file, $targetDirectory = null, $prefix = null){
    $this->loadImage($path, $file, $prefix);
    $this->targetDirectory = $targetDirectory;
    $this->prefix = $prefix;
    $this->imageCounter = self::getImageCounter($targetDirectory);
  }

  public static function getImageCounter($targetDirectory){
    $imageCounter = -1;
    if(file_exists($targetDirectory)){
      foreach(scandir($targetDirectory) as $file){
        if(preg_match('/^[\da-z]+_(\d+)_(\d+).jpg$/', $file, $match)){
          $imageNumber = (int) $match[1];
          if($imageNumber > $imageCounter){
            $imageCounter = $imageNumber;
          }
        }
      }
    }
    return $imageCounter + 1;
  }

  public function crop($path, $file, $targetDirectory, $prefix){
    $this->configure($path, $file, $targetDirectory, $prefix);

    // make sure we have valid source image !!!
    // make sure its big enough for the crop !!!

    $coordinates = array(
      'middle' => array('x' => ($this->width  / 2.0) - (self::THUMBNAIL_SIZE / 2.0), 'y' => ($this->height  / 2.0) - (self::THUMBNAIL_SIZE / 2.0)),
      'upper left' => array('x' => 0, 'y' => 0),
      'upper right' => array('x' => $this->width - self::THUMBNAIL_SIZE, 'y' => 0),
      'bottom left' => array('x' => 0, 'y' => $this->height - self::THUMBNAIL_SIZE),
      'bottom right' => array('x' => $this->width - self::THUMBNAIL_SIZE, 'y' => $this->height - self::THUMBNAIL_SIZE),
      'golden ratio ul' => array(
        'x' => $this->width  * (1 - self::GOLDEN_RATIO) - (self::THUMBNAIL_SIZE / 2.0),
        'y' => $this->height * (1 - self::GOLDEN_RATIO) - (self::THUMBNAIL_SIZE / 2.0)
      ),
      'golden ratio ur' => array(
        'x' => $this->width  * (1 - self::GOLDEN_RATIO) - (self::THUMBNAIL_SIZE / 2.0),
        'y' => $this->height * self::GOLDEN_RATIO - (self::THUMBNAIL_SIZE / 2.0)
      ),
      'golden ratio bl' => array(
        'x' => $this->width  * (1 - self::GOLDEN_RATIO) - (self::THUMBNAIL_SIZE / 2.0),
        'y' => $this->height * self::GOLDEN_RATIO - (self::THUMBNAIL_SIZE / 2.0)
      ),
      'golden ratio' => array(
        'x' => $this->width * self::GOLDEN_RATIO - (self::THUMBNAIL_SIZE / 2.0),
        'y' => $this->height * self::GOLDEN_RATIO - (self::THUMBNAIL_SIZE / 2.0)
      )
    );

    foreach($coordinates as $xy){
      $thumbnail = $this->createThumbnail($xy['x'], $xy['y']);
      $this->saveThumbnail($thumbnail);
      $this->destroyThumbnail($thumbnail);
    }
  }

  public function cropSpecial($x, $y, $width, $height, $path, $file, $targetDirectory, $prefix){
    $this->configure($path, $file, $targetDirectory, $prefix);

    $thumbnail = imagecreatetruecolor(self::THUMBNAIL_SIZE, self::THUMBNAIL_SIZE);
    imagecopyresized ($thumbnail, $this->image, 0, 0, $x, $y, self::THUMBNAIL_SIZE, self::THUMBNAIL_SIZE, $width, $height);

    $this->saveThumbnail($thumbnail);
    $this->destroyThumbnail($thumbnail);
  }

  protected function createThumbnail($x = 0, $y = 0, $width = null, $height = null){
    $width = $width ? $width : self::THUMBNAIL_SIZE;
    $height = $height ? $height : self::THUMBNAIL_SIZE;
    $thumbnail = imagecreatetruecolor($width, $height);
    imagecopy($thumbnail, $this->image, 0, 0, $x, $y, $width, $height);

    return $thumbnail;
  }

  protected function saveThumbnail($thumbnail){
    imagejpeg($thumbnail, $this->targetDirectory . '/' . $this->prefix . '_' . $this->imageCounter . '_' . $this->cropCounter++ . '.jpg', 100);
  }

  protected function destroyThumbnail($thumbnail){
    imagedestroy($thumbnail);
  }
  
  public function setRandomMasterSample(){
    $target = $this->targetDirectory . '/' . $this->prefix . '_' . $this->imageCounter . '_0.jpg';
    $link = $this->targetDirectory . '/' . $this->prefix . '.jpg';
    
    if(!file_exists($link)){
      if(!symlink($target, $link)){
        throw new Exception('ImageCropper::setRandomMasterSample> symlink failed (' . $target . ')');
      }
    }
  }
}

?>
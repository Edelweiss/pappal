<?php

namespace App\Service;

use Exception;

class ImageCropper extends ImagePeer{
  public $imageCounter = 0;       // will be set from currently highest number, e.g. 8235b_n_8.jpg => n+1
  public $cropCounter = 0;
  public $targetDirectory = null; // thumbnail/<HGV_FOLDER_1000>/<HGV_ID>
  public $prefix = null;          // usually HGV id

  protected function configure($path, $file, $targetDirectory = null, $prefix = null){
    $this->loadImage($path, $file, $prefix);
    $this->targetDirectory = $targetDirectory;
    $this->prefix = $prefix;
    $this->imageCounter = self::getImageCounter($targetDirectory);
  }

  /*
  8235b_0_0.jpg (thumbnails…)
  8235b_0_1.jpg
  …
  8235b_0_8.jpg
  8235b_1_0.jpg
  8235b_1_1.jpg
  …
  8235b_1_8.jpg
  …  
  8235b_n_0.jpg
  8235b_n_1.jpg
  8235b_n_3.jpg
  …
  8235b_n_8.jpg (thumbnail with highest number)
  8235b.jpg     (master thumbnail)
  8235b_lat.jpg (master thumbnail for Latin)
  8235b_cop.jpg (master thumbnail for Coptic)
  
  returns n+1 (0 if there aren’t any thumbnails yet)
  */
  public static function getImageCounter($targetDirectory){
    $imageCounter = -1;
    if(is_dir($targetDirectory)){
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

  public function crop($sourcePath, $sourceFile, $targetDirectory, $targetPrefix = 'pappal'){
    $this->configure($sourcePath, $sourceFile, $targetDirectory, $targetPrefix);

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
}

?>

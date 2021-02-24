<?php

namespace Papyrillio\PapPalBundle\Service;

use Exception;

class ImagePuncher{
  public $path     = null; // raw/HGV_1000/HGV_ID
  public $file     = null; // filename of image, e.g. POxy.v0042.n3047.a.01.hires.jpg
  public $filepath = null;
  public $width    = null;
  public $height   = null;
  public $mime     = null;
  public $image    = null;
  public $imageCounter = 0;
  public $cropCounter = 0;
  public $targetDirectory = null; // thumbnail/HGV_1000/HGV_ID
  public $prefix = null; // usually HGV id
  public $suffix = null; // usually empty or e.g. »lat« for latin handsamples
  
  public static $THUMBNAIL_SIZE = 300;
  public static $GOLDEN_RATIO = 0.618;
  public static $IMAGE_COUNTER = array();

  public function configure($sourcePath, $sourceFile, $targetDirectory, $prefix, $suffix){
    $this->path = $sourcePath;
    $this->file = $sourceFile;
    $this->filepath = $sourcePath . '/' . $sourceFile;
    $imageSize = getimagesize($this->filepath);
    $this->width = $imageSize[0];
    $this->height = $imageSize[1];
    $this->mime = $imageSize['mime'];
    $this->targetDirectory = $targetDirectory;
    $this->prefix = $prefix;
    $this->suffix = $suffix && $suffix != 'grc' ? $suffix : '';
    $this->cropCounter = 0;

    if(!isset(self::$IMAGE_COUNTER[$prefix])){
      self::$IMAGE_COUNTER[$prefix] = 0;
    }
    $this->imageCounter = self::$IMAGE_COUNTER[$prefix]++;

    if($this->mime == 'image/jpeg'){
      $this->image = imagecreatefromjpeg($this->filepath);
    } else if($this->mime == 'image/png'){
      $this->image = imagecreatefrompng($this->filepath);
    } else if($this->mime == 'image/gif'){
      $this->image = imagecreatefromgif($this->filepath);
    } else {
      throw new Exception('ImageCropper> Unrecognised image format ' . $this->filepath . ' / ' . $this->mime . ' (' . $prefix . ')');
    }

    if(!$this->image){
      throw new Exception('ImageCropper> Image ' . $this->filepath . ' (' . $prefix . ') could not be loaded');
    }
  }

  public function __desctruct(){
    imagedestroy($this->image);
  }

  public function punch($path, $file, $targetDirectory, $prefix, $suffix){
    // make sure we have valid source image !!!
    // make sure its big enough for the crop !!!
    
    $this->configure($path, $file, $targetDirectory, $prefix, $suffix);

    $coordinates = array(
      'middle' => array('x' => ($this->width  / 2.0) - (self::$THUMBNAIL_SIZE / 2.0), 'y' => ($this->height  / 2.0) - (self::$THUMBNAIL_SIZE / 2.0)),
      'upper left' => array('x' => 0, 'y' => 0),
      'upper right' => array('x' => $this->width - self::$THUMBNAIL_SIZE, 'y' => 0),
      'bottom left' => array('x' => 0, 'y' => $this->height - self::$THUMBNAIL_SIZE),
      'bottom right' => array('x' => $this->width - self::$THUMBNAIL_SIZE, 'y' => $this->height - self::$THUMBNAIL_SIZE),
      'golden ratio ul' => array(
        'x' => $this->width  * (1 - self::$GOLDEN_RATIO) - (self::$THUMBNAIL_SIZE / 2.0),
        'y' => $this->height * (1 - self::$GOLDEN_RATIO) - (self::$THUMBNAIL_SIZE / 2.0)
      ),
      'golden ratio ur' => array(
        'x' => $this->width  * (1 - self::$GOLDEN_RATIO) - (self::$THUMBNAIL_SIZE / 2.0),
        'y' => $this->height * self::$GOLDEN_RATIO - (self::$THUMBNAIL_SIZE / 2.0)
      ),
      'golden ratio bl' => array(
        'x' => $this->width  * (1 - self::$GOLDEN_RATIO) - (self::$THUMBNAIL_SIZE / 2.0),
        'y' => $this->height * self::$GOLDEN_RATIO - (self::$THUMBNAIL_SIZE / 2.0)
      ),
      'golden ratio' => array(
        'x' => $this->width * self::$GOLDEN_RATIO - (self::$THUMBNAIL_SIZE / 2.0),
        'y' => $this->height * self::$GOLDEN_RATIO - (self::$THUMBNAIL_SIZE / 2.0)
      )
    );

    foreach($coordinates as $xy){
      $thumbnail = $this->createThumbnail($xy['x'], $xy['y']);
      $this->saveThumbnail($thumbnail);
      $this->destroyThumbnail($thumbnail);
    }
  }
  
  protected function createThumbnail($x = 0, $y = 0, $width = null, $height = null){
    $width = $width ? $width : self::$THUMBNAIL_SIZE;
    $height = $height ? $height : self::$THUMBNAIL_SIZE;
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
    $link = $this->targetDirectory . '/' . $this->prefix . $this->suffix . '.jpg';
    
    if(!file_exists($link)){
      if(!symlink($target, $link)){
        throw new Exception('ImageCropper::setRandomMasterSample> symlink failed (' . $target . ')');
      }
    }
  }

  public function __toString(){
    return $this->filepath . ' ' . $this->width . 'x' . $this->height . ' (' . $this->mime . ')';
  }
}

?>
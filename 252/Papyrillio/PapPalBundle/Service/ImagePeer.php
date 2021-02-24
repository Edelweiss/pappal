<?php

namespace Papyrillio\PapPalBundle\Service;

use Exception;

class ImagePeer{
  const DIRECTION_CLOCKWISE        = -90;
  const DIRECTION_COUNTERCLOCKWISE = 90;
  const THUMBNAIL_SIZE = 300;
  const GOLDEN_RATIO = 0.618;

  public $path     = null; // raw/HGV_1000/HGV_ID
  public $file     = null; // filename of image, e.g. POxy.v0042.n3047.a.01.hires.jpg
  public $filepath = null;
  
  public $width    = null; // will be retrieved from the image file
  public $height   = null;
  public $mime     = null;
  public $image    = null;

  protected function setFilepath($path, $file){
    $this->path = $path;
    $this->file = $file;
    return $this->filepath = $path . '/' . $file;
  }
  
  protected function loadImage($path, $file, $prefix = ''){
    $this->setFilepath($path, $file);
    $imageSize = getimagesize($this->filepath);
    $this->width = $imageSize[0];
    $this->height = $imageSize[1];
    $this->mime = $imageSize['mime'];
    
    if($this->mime == 'image/jpeg'){
      $this->image = imagecreatefromjpeg($this->filepath);
    } else if($this->mime == 'image/png'){
      $this->image = imagecreatefrompng($this->filepath);
    } else if($this->mime == 'image/gif'){
      $this->image = imagecreatefromgif($this->filepath);
    } else {
      throw new Exception('ImagePeer> Unrecognised image format ' . $this->filepath . ' / ' . $this->mime . (!empty($prefix) ? ' (' . $prefix . ')' : ''));
    }

    if(!$this->image){
      throw new Exception('ImagePeer> Image ' . $this->filepath . (!empty($prefix) ? ' (' . $prefix . ')' : '') . ' could not be loaded');
    }

    return $this->image;
  }

  protected function configure($path, $file){
    $this->loadImage($path, $file); 
  }

  public function __desctruct(){
    imagedestroy($this->image);
  }

  public function __toString(){
    return $this->filepath . ' ' . $this->width . 'x' . $this->height . ' (' . $this->mime . ')';
  }
}

?>
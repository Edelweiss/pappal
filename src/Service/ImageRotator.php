<?php

namespace App\Service;

class ImageRotator extends ImagePeer{
  public $direction = null;

  protected function configure($path, $file, $direction = null){
    $this->loadImage($path, $file);
    $this->direction = $direction == ImagePeer::DIRECTION_CLOCKWISE ? $direction : ImagePeer::DIRECTION_COUNTERCLOCKWISE; 
  }

  public function rotate($path, $file, $direction){
    $this->configure($path, $file, $direction);
    $rotatedImage = imagerotate($this->image, $direction, 0);
    imagejpeg($rotatedImage, $this->filepath, 100);
  }
}

?>
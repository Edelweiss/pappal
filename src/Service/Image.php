<?php

namespace App\Service;

class Image{
  public $url;
  public $name;
  public $description;

  public function __construct($url, $name, $description){
    $this->url = $url;
    $this->name = $name;
    $this->description = $description;
  }
  
  public function __toString(){
    return $this->name . ': ' . $this->description . ' (' . $this->url . ')';
  }
}

?>
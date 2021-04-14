<?php

namespace App\Entity;

use App\Repository\ThumbnailRepository;

class Thumbnail {
    private $id;
    private $language;
    private $file;
    private $sample;
    
    public function __construct($language = null){
      if($language){
        $this->setLanguage($language);
      }
    }

    public function getId() {
        return $this->id;
    }

    /**
     * Set language -> also updates member »file«
     */
    public function setLanguage($language) {
        if(in_array($language, array('grc', 'lat','cop','egy','ara'))){
          if($this->file){
            $this->file = preg_replace('/(lat|cop|egy|ara)/', $language != 'grc' ? $language : '', $this->file);
          } else if ($this->sample){
            $this->file = $sample->getFolder() . '/' . $sample->getHgv() . '/' . $sample->getHgv() . ($language != 'grc' ? $language : '') . '.jpg';
          }
          $this->language = $language;
        }
    }

    public function getLanguage() {
        return $this->language;
    }

    public function setFile($file) {
        $this->file = $file;
    }

    public function getFile() {
        return $this->file;
    }

    /**
     * Set sample -> also upates member variable »file«
     */
    public function setSample(\App\Entity\Sample $sample) {
        $this->sample = $sample;
        $this->file = $sample->getFolder() . '/' . $sample->getHgv() . '/' . $sample->getHgv() . ($this->language != 'grc' ? $this->language : '') . '.jpg';
    }

    public function getSample() {
        return $this->sample;
    }
}
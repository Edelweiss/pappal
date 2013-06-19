<?php

namespace Papyrillio\PapPalBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Papyrillio\PapPalBundle\Entity\Thumbnail
 */
class Thumbnail
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var text $language
     */
    private $language;

    /**
     * @var text $file
     */
    private $file;

    /**
     * @var Papyrillio\PapPalBundle\Entity\Sample
     */
    private $sample;
    
    public function __construct($language = 'grc'){
      $this->setLanguage($language);
    }


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set language -> also updates member »file«
     *
     * @param text $language
     */
    public function setLanguage($language)
    {
        if(in_array($language, array('lat','cop','egy','ara'))){
          if($this->file){
            $this->file = preg_replace('/(lat|cop|egy|ara)/', $language != 'grc' ? $language : '', $this->file);
          } else if ($this->sample){
            $this->file = $sample->getFolder() . '/' . $sample->getHgv() . '/' . $sample->getHgv() . ($language != 'grc' ? $language : '') . '.jpg';
          }
          $this->language = $language;
        }
    }

    /**
     * Get language
     *
     * @return text 
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set file
     *
     * @param text $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * Get file
     *
     * @return text 
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set sample -> also upates member variable »file«
     *
     * @param Papyrillio\PapPalBundle\Entity\Sample $sample
     */
    public function setSample(\Papyrillio\PapPalBundle\Entity\Sample $sample)
    {
        $this->sample = $sample;
        $this->file = $sample->getFolder() . '/' . $sample->getHgv() . '/' . $sample->getHgv() . ($this->language != 'grc' ? $this->language : '') . '.jpg';
    }

    /**
     * Get sample
     *
     * @return Papyrillio\PapPalBundle\Entity\Sample 
     */
    public function getSample()
    {
        return $this->sample;
    }
}
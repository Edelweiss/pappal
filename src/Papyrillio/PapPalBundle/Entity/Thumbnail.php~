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
     * Set language
     *
     * @param text $language
     */
    public function setLanguage($language)
    {
        $this->language = $language;
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
     * Set sample
     *
     * @param Papyrillio\PapPalBundle\Entity\Sample $sample
     */
    public function setSample(\Papyrillio\PapPalBundle\Entity\Sample $sample)
    {
        $this->sample = $sample;
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
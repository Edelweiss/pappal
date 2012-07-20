<?php

namespace Papyrillio\PapPalBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Papyrillio\PapPalBundle\Entity\Sample
 */
class Sample
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var integer $tm
     */
    private $tm;

    /**
     * @var integer $folder
     */
    private $folder;

    /**
     * @var string $hgv
     */
    private $hgv;

    /**
     * @var string $ddb
     */
    private $ddb;

    /**
     * @var string $collection
     */
    private $collection;

    /**
     * @var string $volume
     */
    private $volume;

    /**
     * @var string $document
     */
    private $document;

    /**
     * @var date $dateWhen
     */
    private $dateWhen;

    /**
     * @var date $dateNotBefore
     */
    private $dateNotBefore;

    /**
     * @var date $dateAfter
     */
    private $dateAfter;

    /**
     * @var text $dateHgvFormat
     */
    private $dateHgvFormat;

    /**
     * @var integer $century
     */
    private $century;

    /**
     * @var integer $year
     */
    private $year;

    /**
     * @var integer $month
     */
    private $month;

    /**
     * @var integer $day
     */
    private $day;

    /**
     * @var text $title
     */
    private $title;

    /**
     * @var text $material
     */
    private $material;

    /**
     * @var text $keywords
     */
    private $keywords;

    /**
     * @var text $digitalImages
     */
    private $digitalImages;

    /**
     * @var text $provenance
     */
    private $provenance;

    /**
     * @var date $dateSort
     */
    private $dateSort;

    /**
     * @var string $status
     */
    private $status;

    /**
     * @var Papyrillio\PapPalBundle\Entity\Comment
     */
    private $comments;
    
    public function getThumbnail(){
      return 'thumbnail/' . $this->folder . '/' . $this->hgv . '/' . $this->hgv . '.jpg';
    }
    
    public function getThumbnails(){
      $thumbnails = array();

      foreach(scandir(__DIR__ . '/../../../../web/thumbnail/' . $this->folder . '/' . $this->hgv) as $file){
        if(preg_match('/(' . $this->hgv . '_\d+_\d+\.jpg)$/', $file, $matches)){
          $thumbnails[] = 'thumbnail/' . $this->folder . '/' . $this->hgv . '/' . $matches[1];
        }
      }
      return $thumbnails;
    }

    public function getImageLinks(){
      return explode(', ', $this->digitalImages);
    }

    public function setMasterThumbnail($masterThumbnail){
      $dir = __DIR__ . '/../../../../web/thumbnail/' . $this->folder . '/' . $this->hgv;
      $dir = '/Users/Admin/PapPal/images/thumbnail/' . $this->folder . '/' . $this->hgv;
      $masterThumbnail = $dir . '/' . $masterThumbnail;
      $link = $dir . '/' . $this->hgv . '.jpg';
      if(file_exists($masterThumbnail)){
        if(file_exists($link)){
          unlink($link);
        }
        if(symlink($masterThumbnail, $link)){
          return true;
        }
      }
      return false;
    }

    public function __construct()
    {
        $this->comments = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set tm
     *
     * @param integer $tm
     */
    public function setTm($tm)
    {
        $this->tm = $tm;
    }

    /**
     * Get tm
     *
     * @return integer 
     */
    public function getTm()
    {
        return $this->tm;
    }

    /**
     * Set folder
     *
     * @param integer $folder
     */
    public function setFolder($folder)
    {
        $this->folder = $folder;
    }

    /**
     * Get folder
     *
     * @return integer 
     */
    public function getFolder()
    {
        return $this->folder;
    }

    /**
     * Set hgv
     *
     * @param string $hgv
     */
    public function setHgv($hgv)
    {
        $this->hgv = $hgv;
    }

    /**
     * Get hgv
     *
     * @return string 
     */
    public function getHgv()
    {
        return $this->hgv;
    }

    /**
     * Set ddb
     *
     * @param string $ddb
     */
    public function setDdb($ddb)
    {
        $this->ddb = $ddb;
    }

    /**
     * Get ddb
     *
     * @return string 
     */
    public function getDdb()
    {
        return $this->ddb;
    }

    /**
     * Set collection
     *
     * @param string $collection
     */
    public function setCollection($collection)
    {
        $this->collection = $collection;
    }

    /**
     * Get collection
     *
     * @return string 
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * Set volume
     *
     * @param string $volume
     */
    public function setVolume($volume)
    {
        $this->volume = $volume;
    }

    /**
     * Get volume
     *
     * @return string 
     */
    public function getVolume()
    {
        return $this->volume;
    }

    /**
     * Set document
     *
     * @param string $document
     */
    public function setDocument($document)
    {
        $this->document = $document;
    }

    /**
     * Get document
     *
     * @return string 
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Set dateWhen
     *
     * @param date $dateWhen
     */
    public function setDateWhen($dateWhen)
    {
        $this->dateWhen = $dateWhen;
    }

    /**
     * Get dateWhen
     *
     * @return date 
     */
    public function getDateWhen()
    {
        return $this->dateWhen;
    }

    /**
     * Set dateNotBefore
     *
     * @param date $dateNotBefore
     */
    public function setDateNotBefore($dateNotBefore)
    {
        $this->dateNotBefore = $dateNotBefore;
    }

    /**
     * Get dateNotBefore
     *
     * @return date 
     */
    public function getDateNotBefore()
    {
        return $this->dateNotBefore;
    }

    /**
     * Set dateAfter
     *
     * @param date $dateAfter
     */
    public function setDateAfter($dateAfter)
    {
        $this->dateAfter = $dateAfter;
    }

    /**
     * Get dateAfter
     *
     * @return date 
     */
    public function getDateAfter()
    {
        return $this->dateAfter;
    }

    /**
     * Set dateHgvFormat
     *
     * @param text $dateHgvFormat
     */
    public function setDateHgvFormat($dateHgvFormat)
    {
        $this->dateHgvFormat = $dateHgvFormat;
    }

    /**
     * Get dateHgvFormat
     *
     * @return text 
     */
    public function getDateHgvFormat()
    {
        return $this->dateHgvFormat;
    }

    /**
     * Set century
     *
     * @param integer $century
     */
    public function setCentury($century)
    {
        $this->century = $century;
    }

    /**
     * Get century
     *
     * @return integer 
     */
    public function getCentury()
    {
        return $this->century;
    }

    /**
     * Set year
     *
     * @param integer $year
     */
    public function setYear($year)
    {
        $this->year = $year;
    }

    /**
     * Get year
     *
     * @return integer 
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * Set month
     *
     * @param integer $month
     */
    public function setMonth($month)
    {
        $this->month = $month;
    }

    /**
     * Get month
     *
     * @return integer 
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * Set day
     *
     * @param integer $day
     */
    public function setDay($day)
    {
        $this->day = $day;
    }

    /**
     * Get day
     *
     * @return integer 
     */
    public function getDay()
    {
        return $this->day;
    }

    /**
     * Set title
     *
     * @param text $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get title
     *
     * @return text 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set material
     *
     * @param text $material
     */
    public function setMaterial($material)
    {
        $this->material = $material;
    }

    /**
     * Get material
     *
     * @return text 
     */
    public function getMaterial()
    {
        return $this->material;
    }

    /**
     * Set keywords
     *
     * @param text $keywords
     */
    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;
    }

    /**
     * Get keywords
     *
     * @return text 
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * Set digitalImages
     *
     * @param text $digitalImages
     */
    public function setDigitalImages($digitalImages)
    {
        $this->digitalImages = $digitalImages;
    }

    /**
     * Get digitalImages
     *
     * @return text 
     */
    public function getDigitalImages()
    {
        return $this->digitalImages;
    }

    /**
     * Set provenance
     *
     * @param text $provenance
     */
    public function setProvenance($provenance)
    {
        $this->provenance = $provenance;
    }

    /**
     * Get provenance
     *
     * @return text 
     */
    public function getProvenance()
    {
        return $this->provenance;
    }

    /**
     * Set dateSort
     *
     * @param integer $dateSort
     */
    public function setDateSort($dateSort)
    {
        $this->dateSort = $dateSort;
    }

    /**
     * Get dateSort
     *
     * @return date 
     */
    public function getDateSort()
    {
        return $this->dateSort;
    }

    /**
     * Set status
     *
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Add comments
     *
     * @param Papyrillio\PapPalBundle\Entity\Comment $comments
     */
    public function addComment(\Papyrillio\PapPalBundle\Entity\Comment $comments)
    {
        $this->comments[] = $comments;
    }

    /**
     * Get comments
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getComments()
    {
        return $this->comments;
    }
    /**
     * @var datetime $importDate
     */
    private $importDate;


    /**
     * Set importDate
     *
     * @param datetime $importDate
     */
    public function setImportDate($importDate)
    {
        $this->importDate = $importDate;
    }

    /**
     * Get importDate
     *
     * @return datetime 
     */
    public function getImportDate()
    {
        return $this->importDate;
    }
}
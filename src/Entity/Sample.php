<?php

namespace App\Entity;

use App\Repository\SampleRepository;
use DateTime;

class Sample
{
    private $id;
    private $tm;
    private $folder;
    private $hgv;
    private $ddb;
    private $collection;
    private $volume;
    private $document;
    private $dateWhen;
    private $dateNotBefore;
    private $dateNotAfter;
    private $dateHgvFormat;
    private $century;
    private $year;
    private $month;
    private $day;
    private $title;
    private $material;
    private $keywords;
    private $digitalImages;
    private $provenance;
    private $dateSort;
    private $status;
    private $comments;
    private $thumbnails;
    private $importDate;

    private const THUMBNAIL_DIR = '/mnt/sds_cifs/pappal/thumbnail';
    private const SAMPLE_DIR    = '/mnt/sds_cifs/pappal/sample';

    public function updateDateSort(){
      $this->setDateSort(
        self::generateDateSortKey($this->dateWhen ? $this->dateWhen : ($this->dateNotBefore ? $this->dateNotBefore : $this->dateNotAfter))
      );
    }

    public function getThumbnail($fullpath = false){
      return ($fullpath ? Sample::THUMBNAIL_DIR . '/' : 'thumbnail/') . $this->folder . '/' . $this->hgv . '/' . $this->hgv . '.jpg';
    }

    public function getThumbnailList($fullpath = false){
      $thumbnailList = array();
      
      foreach($this->thumbnails as $thumbnail){
        $thumbnailList[$thumbnail->getLanguage()] = ($fullpath ? Sample::THUMBNAIL_DIR . '/' : 'thumbnail/') . $this->folder . '/' . $this->hgv . '/' . $this->hgv . ($thumbnail->getLanguage() !== 'grc' ? $thumbnail->getLanguage() : '') . '.jpg';
      }

      return $thumbnailList; //($fullpath ? readlink(Sample::THUMBNAIL_DIR) . '/' : 'thumbnail/') . $this->folder . '/' . $this->hgv . '/' . $this->hgv . '.jpg';
    }

    public function getThumbnailStash(){
      $thumbnails = array();
      $thumbnailDirectory = Sample::THUMBNAIL_DIR . '/' . $this->folder . '/' . $this->hgv;

      if(file_exists($thumbnailDirectory)){
        foreach(scandir($thumbnailDirectory) as $file){
          if(preg_match('/(' . $this->hgv . '_\d+_\d+\.jpg)$/', $file, $matches)){
            $thumbnails[$matches[1]] = 'thumbnail/' . $this->folder . '/' . $this->hgv . '/' . $matches[1];
          }
        }
      }
      return $thumbnails;
    }

    public function setMasterThumbnail($masterThumbnail, $language = 'grc'){ // only in the filesystem, should be part of the controller
      $dir = Sample::THUMBNAIL_DIR . '/' . $this->folder . '/' . $this->hgv;
      $link = $this->hgv . ($language != 'grc' ? $language : '') . '.jpg';
      if(file_exists($dir . '/' . $masterThumbnail)){
        if(file_exists($dir . '/' . $link)){
          unlink($dir . '/' . $link);
        }
        exec('cd ' . $dir . '; cp ' . $masterThumbnail . ' ' . $link);
        return true;
      }
      return false;
    }

    public function unsetMasterThumbnail($language = 'grc'){ // only in the filesystem, should be part of the controller
      $dir = Sample::THUMBNAIL_DIR . '/' . $this->folder . '/' . $this->hgv;
      $link = $dir . '/' . $this->hgv . ($language != 'grc' ? $language : '') . '.jpg';

      if(file_exists($link)){
        return unlink($link);
      }

      return true;
    }
    
    public function getThumbnailByLanguage($language){
      foreach($this->thumbnails as $thumbnail){
        if($thumbnail->getLanguage() == $language){
          return $thumbnail;
        }
      }
      return null;
    }

    public function getImageLinks(){
      return explode(', ', $this->digitalImages);
    }
    /*
     * return Array [FILENAME] => [RELATIVE FILEPATH], e.g. array(1) { ["X-1406_0.jpg"]=> string(28) "sample/22/21346/X-1406_0.jpg" }  
     * */
    public function getUploadedImages(){
      $imageLinks = array();
      $imageDirectory = Sample::SAMPLE_DIR . '/' . $this->folder . '/' . $this->hgv;

      if(file_exists($imageDirectory)){
        foreach(scandir($imageDirectory) as $file){
          if(preg_match('/^(.+\.jpg)$/', $file, $matches)){
            $imageLinks[$matches[1]] = 'sample/' . $this->folder . '/' . $this->hgv . '/' . $matches[1];
          }
        }
      }
      return $imageLinks;
    }

    public function __construct()
    {
        $this->comments = new \Doctrine\Common\Collections\ArrayCollection();
        $this->thumbnails = new \Doctrine\Common\Collections\ArrayCollection();
        $this->status = 'ok';
        $this->importDate = new DateTime(); // date('Y-m-d H:i:s');
    }

    public function getId()
    {
        return $this->id;
    }

    public function setTm($tm)
    {
        $this->tm = $tm * 1;
        $this->setFolder(ceil($this->tm / 1000.0));
    }

    public function getTm()
    {
        return $this->tm;
    }

    public function setFolder($folder)
    {
        $this->folder = (int) $folder;
    }

    public function getFolder()
    {
        return $this->folder;
    }

    public function setHgv($hgv)
    {
        $this->setTm(preg_replace('/[^\d]+/', '', $hgv));
        $this->hgv = $hgv;
    }

    public function getHgv()
    {
        return $this->hgv;
    }

    public function setDdb($ddb)
    {
        $this->ddb = $ddb;

        $tokenList = explode(';', $this->ddb);
        $this->collection = array_key_exists(0, $tokenList) ? $tokenList[0] : '';
        $this->volume     = array_key_exists(1, $tokenList) ? $tokenList[1] : '';
        $this->document   = array_key_exists(2, $tokenList) ? $tokenList[2] : '';
    }

    public function getDdb()
    {
        return $this->ddb;
    }

    public function setCollection($collection)
    {
        $this->collection = $collection;
    }

    public function getCollection()
    {
        return $this->collection;
    }

    public function setVolume($volume)
    {
        $this->volume = $volume;
    }

    public function getVolume()
    {
        return $this->volume;
    }

    public function setDocument($document)
    {
        $this->document = $document;
    }

    public function getDocument()
    {
        return $this->document;
    }

    public function setDateWhen($date)
    {
        $date = trim($date);
        if($date && !empty($date)){
          $this->dateWhen = $date;
          $this->updateDateSort();
        }
    }

    public function getDateWhen()
    {
        return $this->dateWhen;
    }

    public function setDateNotBefore($date)
    {
        $date = trim($date);
        if($date && !empty($date)){
          $this->dateNotBefore = $date;
          $this->updateDateSort();
        }
    }

    public function getDateNotBefore()
    {
        return $this->dateNotBefore;
    }

    public function setDateNotAfter($date)
    {
        $date = trim($date);
        if($date && !empty($date)){
          $this->dateNotAfter = $date;
          $this->updateDateSort();
        }
    }

    public function getDateNotAfter()
    {
        return $this->dateNotAfter;
    }

    public function setDateHgvFormat($dateHgvFormat)
    {
        $this->dateHgvFormat = $dateHgvFormat;
    }

    public function getDateHgvFormat()
    {
        return $this->dateHgvFormat;
    }

    public function setCentury($century)
    {
        $this->century = $century;
    }

    public function getCentury()
    {
        return $this->century;
    }

    public function setYear($year)
    {
        $this->year = $year;
    }

    public function getYear()
    {
        return $this->year;
    }

    public function setMonth($month)
    {
        $this->month = $month;
    }

    public function getMonth()
    {
        return $this->month;
    }

    public function setDay($day)
    {
        $this->day = $day;
    }

    public function getDay()
    {
        return $this->day;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setMaterial($material)
    {
        $this->material = $material;
    }

    public function getMaterial()
    {
        return $this->material;
    }

    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;
    }

    public function getKeywords()
    {
        return $this->keywords;
    }

    public function setDigitalImages($digitalImages)
    {
        $this->digitalImages = $digitalImages;
    }

    public function getDigitalImages()
    {
        return $this->digitalImages;
    }

    public function setProvenance($provenance)
    {
        $this->provenance = $provenance;
    }

    public function getProvenance()
    {
        return $this->provenance;
    }

    public function setDateSort($dateSort)
    {
        $this->dateSort = $dateSort;
    }

    public function getDateSort()
    {
        return $this->dateSort;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function addComment(\App\Entity\Comment $comments)
    {
        $this->comments[] = $comments;
    }

    public function getComments()
    {
        return $this->comments;
    }

    public function addThumbnail(\App\Entity\Thumbnail $thumbnails)
    {
        $this->thumbnails[] = $thumbnails;
    }

    public function getThumbnails()
    {
        return $this->thumbnails;
    }

    public function setImportDate($importDate)
    {
        $this->importDate = $importDate;
    }

    public function getImportDate()
    {
        return $this->importDate;
    }

    public static function generateDateSortKey($dateSort){
      $dateSortKey = 0;
      if($dateSort){

        $sign = 1;
        if(strpos($dateSort, '-') === 0){
          $sign = -1;
          $dateSort = substr($dateSort, 1);
        }

        $dateSort      = explode('-', $dateSort);
        $dateSortYear  = isset($dateSort[0]) ? $dateSort[0] : '0000';
        $dateSortMonth = isset($dateSort[1]) ? ($sign > 0 ? $dateSort[1] : str_pad(13 - $dateSort[1], 2, '0', STR_PAD_LEFT)) : '00';
        $dateSortDay   = isset($dateSort[2]) ? ($sign > 0 ? $dateSort[2] : str_pad(31 - $dateSort[2], 2, '0', STR_PAD_LEFT)) : '00';
        //$dateSortMonth = isset($dateSort[1]) ? $dateSort[1] : '00';
        //$dateSortDay   = isset($dateSort[2]) ? $dateSort[2] : '00';

        $dateSortKey = ($dateSortYear . $dateSortMonth . $dateSortDay ) * $sign;
      }
      return $dateSortKey;
    }

    public static function makeIsoYear($year){
      $sign = '';
      if(strpos($year, '-') === 0){
        $sign = '-';
        $year = substr($year, 1);
      }
      return $sign . str_pad($year, 4, '0', STR_PAD_LEFT);
    }
}

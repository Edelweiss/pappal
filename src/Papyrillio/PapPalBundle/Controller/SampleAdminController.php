<?php

namespace Papyrillio\PapPalBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Papyrillio\PapPalBundle\Entity\Sample;
use Papyrillio\PapPalBundle\Entity\Thumbnail;
use Papyrillio\PapPalBundle\Entity\Commet;
use Papyrillio\UserBundle\Entity\User;
use Papyrillio\PapPalBundle\Service\ImagePeer;
use DateTime;
use Date;
use DomDocument;
use DOMXPath;
use DOMNodeList;

class SampleAdminController extends PapPalController{

  public function createAction(){
    $error = null;
    
    if($this->get('request')->getMethod() == 'POST'){
      $entityManager = $this->getDoctrine()->getEntityManager();
      $repository = $entityManager->getRepository('PapyrillioPapPalBundle:Sample');
      $hgv = trim($this->getParameter('hgv'));

      if($sample = $repository->findOneBy(array('hgv' => $hgv))){ // ERROR! sample already exists
        $this->get('session')->setFlash('notice', 'Sample data for hgv id #' . $hgv . ' already exists.');
        return new RedirectResponse($this->generateUrl('PapyrillioPapPalBundle_SampleShow', array('id' => $sample->getId())));
      }
      
      // 1. get meta data
      if(($xml = @file_get_contents('http://www.papyri.info/hgv/' . $hgv . '/source')) !== FALSE){

        // 2. parse XML, set meta data
        if($xpath =  new EpiDocPath($xml)){
          $sample = new Sample();
          $sample->setHgv($hgv);
          $sample->setDdb($xpath->getDdb());
          $sample->setDateHgvFormat($xpath->getDate());
          $sample->setDateWhen($xpath->getDateWhen());
          $sample->setDateNotBefore($xpath->getDateNotBefore());
          $sample->setDateNotAfter($xpath->getDateNotAfter());
          $sample->setTitle($xpath->getTitle());
          $sample->setMaterial($xpath->getMaterial());
          $sample->setKeywords($xpath->getKeywords());
          //$sample->setDigitalImages($xpath->getDigitalImages());
          $sample->setDigitalImages('http://adore.ugent.be/OpenURL/app?id=archive.ugent.be:86EE9CA6-6FF9-11E1-B140-E3AB3B7C8C91&type=carousel');
          $sample->setProvenance($xpath->getProvenance());

          if(1) {//if($sample->getDigitalImages() && $sample->getHgvFormat()){
            // 3. download images
            // 4. create thumbnails

            // 5. determine language from meta data (greek by default)
            $notes = $xpath->getNotes();
            $language = 'grc';
            if(preg_match('/^Griechisch(\.| ?-)/', $notes)){
              $language = 'grc';
            } elseif(preg_match('/^Lateinisch(\.| ?-)/', $notes)){
              $language = 'lat';
            }

            // 6. set master thumbnail
            $thumbnail = new Thumbnail($language);
            $thumbnail->setSample($sample);

            // 7. insert into database
            $entityManager->persist($sample);
            $entityManager->persist($thumbnail);
            $entityManager->flush();
            
            // success
            $this->get('session')->setFlash('notice', 'Thumbnail for hgv #' . $hgv . ' was created.');
            return new RedirectResponse($this->generateUrl('PapyrillioPapPalBundle_SampleShow', array('id' => $sample->getId())));
          } else {
            $error = 'No digital images available for #' . $hgv . ' (http://www.papyri.info/hgv/' . $hgv . '/source).';
          }          
                    
        } else { // ERROR! XML XPATH
          $error = 'XML file for hgv id #' . $hgv . ' cannot parsed (http://www.papyri.info/hgv/' . $hgv . '/source).';
        }        
      } else { // ERROR! hgv ids does not exists resp. cannot read from number server
        $error = 'Source file for hgv id #' . $hgv . ' cannot be retrieved from papyri.info (http://www.papyri.info/hgv/' . $hgv . '/source).';
      }      
    }
    
    return $this->render('PapyrillioPapPalBundle:SampleAdmin:create.html.twig', array('error' => $error));

  }
}

class EpiDocPath extends DOMXPath {
  const NAMESPACE_TEI = 'http://www.tei-c.org/ns/1.0';

  public function __construct($xml){
    $doc = new DOMDocument();
    $doc->loadXML($xml);
    parent::__construct($doc);
    $this->registerNamespace('tei', self::NAMESPACE_TEI);
  }
  
  public function getDdb(){
    return $this->getInformation('/tei:TEI/tei:teiHeader/tei:fileDesc/tei:publicationStmt/tei:idno[@type="ddb-hybrid"]');
  }
  
  public function getTitle(){
    return $this->getInformation('/tei:TEI/tei:teiHeader/tei:fileDesc/tei:titleStmt/tei:title');
  }
  
  public function getDate(){
    return $this->getInformation('/tei:TEI/tei:teiHeader/tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:history/tei:origin/tei:origDate');
  }
  
  public function getDateWhen(){
    return $this->getInformation('/tei:TEI/tei:teiHeader/tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:history/tei:origin/tei:origDate/@when');
  }
  
  public function getDateNotBefore(){
    return $this->getInformation('/tei:TEI/tei:teiHeader/tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:history/tei:origin/tei:origDate/@notBefore');
  }
  
  public function getDateNotAfter(){
    return $this->getInformation('/tei:TEI/tei:teiHeader/tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:history/tei:origin/tei:origDate/@notAfter');
  }
  
  public function getMaterial(){
    return $this->getInformation('/tei:TEI/tei:teiHeader/tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:physDesc/tei:objectDesc/tei:supportDesc/tei:support/tei:material');
  }
  
  public function getKeywords(){
    return $this->getFlattenedInformation('/tei:TEI/tei:teiHeader/tei:profileDesc/tei:textClass/tei:keywords[@scheme="hgv"]/tei:term');
  }
  
  public function getDigitalImages(){
    return $this->getFlattenedInformation('/tei:TEI/tei:text/tei:body/tei:div[@type="figure"]/tei:p/tei:figure/tei:graphic/@url');
  }
  
  public function getProvenance(){
    return $this->getInformation('/tei:TEI/tei:teiHeader/tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:history/tei:origin/tei:origPlace');
  }
  
  public function getNotes(){
    return $this->getInformation('/tei:TEI/tei:text/tei:body/tei:div[@type="commentary"][@subtype="general"]/tei:p');
  }

  public function getInformation($xpath, $default = null){
    $result = $this->evaluate($xpath);
    return $result->length > 0 ? trim($result->item(0)->nodeValue) : $default;
  }

  public function getFlattenedInformation($xpath, $default = null){
    if(($nodeList = $this->evaluate($xpath)) && ($nodeList->length > 0)){
      $result = '';
      foreach($nodeList as $node){
        $result .= $node->nodeValue . ', ';
      }
      return rtrim($result, ', ');
    }
    return $default;
  }
}
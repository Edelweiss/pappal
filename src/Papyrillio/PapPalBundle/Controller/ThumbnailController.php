<?php

namespace Papyrillio\PapPalBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Papyrillio\PapPalBundle\Entity\Sample;
use Papyrillio\PapPalBundle\Entity\Thumbnail;
use Papyrillio\PapPalBundle\Form\Type\ThumbnailType;
use DateTime;
use Date;

class ThumbnailController extends PapPalController{

  protected function getFilter(){
    $result = null;

    if($thumbnail = $this->getParameter('thumbnail')){
      $result = array_merge(array('s' => $thumbnail['sample']), array('t' => array('language' => $thumbnail['language'])));

      $this->getRequest()->getSession()->set('thumbnailFilter', $result); // save to session

    } elseif($this->getRequest()->getSession()->get('thumbnailFilter')){

      $result = $this->getRequest()->getSession()->get('thumbnailFilter'); // retrieve session

    }
    return $result;
  }

  protected function getSort(){
    $result = array();
    if($this->getParameter('sort')){
      foreach($this->getParameter('sort') as $sort){
        if(!empty($sort['value'])){
          $result[$sort['value']] = $sort['direction'];
        }
      }
      
      $this->getRequest()->getSession()->set('sampleSort', $result); // save to session

    } elseif($this->getRequest()->getSession()->get('sampleSort')){

      $result = $this->getRequest()->getSession()->get('sampleSort'); // retrieve session

    }
    return $result;
  }

  public function getTemplate(){
    $template = 'list';
    
    if($this->container->get('request')->get('_route') == 'PapyrillioPapPalBundle_ThumbnailGallery'){

      $template = 'gallery';

    } elseif($this->getParameter('template')){

      $template = $this->getParameter('template');
      $this->getRequest()->getSession()->set('sampleTemplate', $template); // save to session

    } elseif($this->getRequest()->getSession()->get('sampleTemplate')){

      $template = $this->getRequest()->getSession()->get('sampleTemplate'); // retrieve session

    }

    return $template;
  }

  protected function getSearchForm(){
    $form = $this->createForm(new ThumbnailType(), new Thumbnail());
    
    if ($this->getRequest()->getMethod() == 'POST') {

      $form->bindRequest($this->getRequest());

      $this->getRequest()->getSession()->set('thumbnailSearchForm', $this->getRequest()); // save to session

    } elseif ($this->getRequest()->getSession()->get('thumbnailSearchForm')) {

      $form->bindRequest($this->getRequest()->getSession()->get('thumbnailSearchForm')); // retrieve session

    }

    return $form;
  }

  public function listAction(){
    $searchForm = $this->getSearchForm();

    $templateOptions = array('list' => 'Gallery', 'gallery' => 'Slideshow');
    $template = $this->getTemplate(); // DEFAULT or ROUTE or POST or SESSION

    $filter = $this->getFilter(); // DEFAULT or POST or SESSION

    $sort = $this->getSort(); // DEFAULT or POST or SESSION
    $sortOptions = array('' => '', 'hgv' => 'HGV', 'ddb' => 'DDB', 'dateSort' => 'Date', 'title' => 'Title', 'material' => 'Material', 'provenance' => 'Provenance', 'language' => 'Language', 'status' => 'Status', 'importDate' => 'Import Date');
    $sortDirections = array('asc' => 'ascending', 'desc' => 'descending');

    $filterOr = array('status', 'title', 'hgv', 'ddb', 'material', 'provenance', 'keywords', 'language');

    $entityManager = $this->getDoctrine()->getEntityManager();
    $repository = $entityManager->getRepository('PapyrillioPapPalBundle:Thumbnail');

    // ORDER BY

    $orderBy = ' ORDER BY';
    if(count($sort)){

      foreach($sort as $key => $direction){
        $type = $key == 'language' ? 't' : 's';
        if($key == 'hgv'){
          $orderBy .= ' s.tm ' . ($direction === 'desc' ? 'DESC' : 'ASC') . ', s.hgv ' . ($direction === 'desc' ? 'DESC' : 'ASC') . ', ';
        } else {
          $orderBy .= ' ' . $type . '.' . $key . ' ' . ($direction === 'desc' ? 'DESC' : 'ASC') . ', ';
        }
      }
      $orderBy = rtrim($orderBy, ', ');
    } else {
      $orderBy .= ' s.dateSort';
    }

    // WHERE

    $where = ' WHERE s.status = :status';
    $parameters = array('status' => 'ok');

    if($filter){
      // standard fields
      foreach($filter as $type => $properties){
        foreach($properties as $field => $value){
          $value = trim($value);
          if(!empty($value) && in_array($field, $filterOr)){
            $where .= ' AND (';
            $index = 0;
            foreach(explode(' ', $value) as $or){
              if(preg_match('/(ae|oe|ue)/', $or)){
                $valueUmlaut = str_replace(array('ae', 'oe', 'ue'), array('ä', 'ö', 'ü'), $or);
                $where .= '(' . $type . '.' . $field . ' LIKE :' . $field . $index . ' OR ' . $type . '.' . $field . ' LIKE :' . $field . ($index + 1) . ') AND '; // was: OR
                $parameters[$field . ($index++)] = '%' . $or . '%';
                $parameters[$field . ($index++)] = '%' . $valueUmlaut . '%';
              } else {
                $where .= $type . '.' . $field . ' LIKE :' . $field . $index . ' AND '; // was: OR
                $parameters[$field . $index] = '%' . $or . '%';
                $index++;
              }
            }
  
            $where = rtrim($where, ' AND ') .  ')'; // was: OR
          }
        }
  
        // date stuff
        $dateSortWhen      = trim($filter['s']['dateWhen']);
        $dateSortNotBefore = trim($filter['s']['dateNotBefore']);
        $dateSortNotAfter  = trim($filter['s']['dateNotAfter']);
  
        if(strlen($dateSortNotBefore) && strlen($dateSortNotAfter)){ // between
  
          
          $dateSortNotBefore = Sample::generateDateSortKey(Sample::makeIsoYear($dateSortNotBefore) . '-00-00');
          if($dateSortNotAfter < 0){
            $dateSortNotAfter = Sample::generateDateSortKey(Sample::makeIsoYear($dateSortNotAfter) . '-13-31');
          } else {
            $dateSortNotAfter = Sample::generateDateSortKey(Sample::makeIsoYear($dateSortNotAfter) . '-12-31');
          }
  
          $where .= ' AND s.dateSort BETWEEN :dateNotBefore AND :dateNotAfter';
          $parameters['dateNotBefore'] = $dateSortNotBefore;
          $parameters['dateNotAfter'] = $dateSortNotAfter;
        } else if(strlen($dateSortNotBefore)){ // not before
  
          $dateSortNotBefore = Sample::generateDateSortKey(Sample::makeIsoYear($dateSortNotBefore) . '-00-00');
  
          $where .= ' AND s.dateSort >= :dateNotBefore';
          $parameters['dateNotBefore'] = $dateSortNotBefore;
        } else if(strlen($dateSortNotAfter)){ // not after
  
          if($dateSortNotAfter < 0){
            $dateSortNotAfter = Sample::generateDateSortKey(Sample::makeIsoYear($dateSortNotAfter) . '-13-31');
          } else {
            $dateSortNotAfter = Sample::generateDateSortKey(Sample::makeIsoYear($dateSortNotAfter) . '-12-31');
          }
  
          $where .= ' AND s.dateSort <= :dateNotAfter';
          $parameters['dateNotAfter'] = $dateSortNotAfter;
        } else if(strlen($dateSortWhen)){
  
          $dateSortFrom = Sample::generateDateSortKey(Sample::makeIsoYear($dateSortWhen) . '-00-00');
          $dateSortTo = Sample::generateDateSortKey(Sample::makeIsoYear($dateSortWhen) . '-12-31');
  
          if($dateSortWhen < 0){
            $dateSortTo = Sample::generateDateSortKey(Sample::makeIsoYear($dateSortWhen) . '-13-31');
          }
  
          $where .= ' AND s.dateSort BETWEEN :dateFrom AND :dateTo';
          $parameters['dateFrom'] = $dateSortFrom;
          $parameters['dateTo'] = $dateSortTo;
        }
      }
 
    }

    // SELECT
    $query = $entityManager->createQuery('
      SELECT t, s FROM PapyrillioPapPalBundle:Thumbnail t JOIN t.sample s ' . $where . ' ' .$orderBy
    )->setParameters($parameters);

    $templateVariables = array(
      'thumbnails' => $query->getResult(),
      'searchForm' => $searchForm->createView(),
      'template' => $template,
      'templateOptions' => $templateOptions,
      'sort' => $sort,
      'sortOptions' => $sortOptions,
      'sortDirections' => $sortDirections,
      'memo' => $this->getMemo()
    );

    return $this->render('PapyrillioPapPalBundle:Thumbnail:' . $template . '.html.twig', $templateVariables);
  }

}


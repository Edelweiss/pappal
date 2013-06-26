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

class ThumbnailController extends PapPalController{

  protected function getFilterForm(){
    $sample = new Sample();

    $form = $this->createFormBuilder($sample)
      ->add('hgv', 'text', array('required' => false)) // e.g. 1915a
      ->add('ddb', 'text', array('required' => false)) // e.g. bgu
      ->add('dateWhen', 'text', array('required' => false)) // 
      ->add('dateNotBefore', 'text', array('required' => false))
      ->add('dateNotAfter', 'text', array('required' => false))
      ->add('title', 'text', array('required' => false))
      ->add('material', 'choice', array('choices' => array('Papyrus' => 'Papyrus', 'Ostrakon' => 'Ostrakon'), 'preferred_choices' => array(''), 'required' => false))
      //->add('language', 'choice', array('choices' => array('grc' => 'Grieschich', 'lat' => 'Lateinisch'), 'preferred_choices' => array(''), 'required' => false))
      ->add('keywords', 'text', array('required' => false))
      ->add('provenance', 'text', array('required' => false))
      ->getForm();  // digitalImages, status, importDate

    if ($this->getRequest()->getMethod() == 'POST') {

      $form->bindRequest($this->getRequest());

      $this->getRequest()->getSession()->set('sampleFilterForm', $this->getRequest()); // save to session

    } elseif ($this->getRequest()->getSession()->get('sampleFilterForm')) {

      $form->bindRequest($this->getRequest()->getSession()->get('sampleFilterForm')); // retrieve session

    }

    return $form;
  }

  protected function getFilter(){
    $result = null;

    if($this->getParameter('form')){
      $result = $this->getParameter('form');

      $this->getRequest()->getSession()->set('sampleFilter', $result); // save to session

    } elseif($this->getRequest()->getSession()->get('sampleFilter')){

      $result = $this->getRequest()->getSession()->get('sampleFilter'); // retrieve session

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

  public function listAction(){
    $filterForm = $this->getFilterForm(); // DEFAULT or POST or SESSION

    $templateOptions = array('list' => 'Gallery', 'gallery' => 'Slideshow');
    $template = $this->getTemplate(); // DEFAULT or ROUTE or POST or SESSION
    
    $filter = $this->getFilter(); // DEFAULT or POST or SESSION

    $sort = $this->getSort(); // DEFAULT or POST or SESSION
    $sortOptions = array('' => '', 'hgv' => 'HGV', 'ddb' => 'DDB', 'dateSort' => 'Date', 'title' => 'Title', 'material' => 'Material', 'provenance' => 'Provenance', 'status' => 'Status', 'importDate' => 'Import Date');
    $sortDirections = array('asc' => 'ascending', 'desc' => 'descending');

    $filterOr = array('title', 'hgv', 'ddb', 'material', 'provenance', 'keywords', 'status');

    $entityManager = $this->getDoctrine()->getEntityManager();
    $repository = $entityManager->getRepository('PapyrillioPapPalBundle:Thumbnail');

    // ORDER BY

    $orderBy = ' ORDER BY';
    if(count($sort)){

      foreach($sort as $key => $direction){
        if($key == 'hgv'){
          $orderBy .= ' s.tm ' . ($direction === 'desc' ? 'DESC' : 'ASC') . ', s.hgv ' . ($direction === 'desc' ? 'DESC' : 'ASC') . ', ';
        } else {
          $orderBy .= ' s.' . $key . ' ' . ($direction === 'desc' ? 'DESC' : 'ASC') . ', ';
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
      foreach($filter as $field => $value){
        $value = trim($value);
        if(!empty($value) && in_array($field, $filterOr)){
          $where .= ' AND (';
          $index = 0;
          foreach(explode(' ', $value) as $or){
            if(preg_match('/(ae|oe|ue)/', $or)){
              $valueUmlaut = str_replace(array('ae', 'oe', 'ue'), array('ä', 'ö', 'ü'), $or);
              $where .= '(s.' . $field . ' LIKE :' . $field . $index . ' OR s.' . $field . ' LIKE :' . $field . ($index + 1) . ') AND '; // was: OR
              $parameters[$field . ($index++)] = '%' . $or . '%';
              $parameters[$field . ($index++)] = '%' . $valueUmlaut . '%';
            } else {
              $where .= 's.' . $field . ' LIKE :' . $field . $index . ' AND '; // was: OR
              $parameters[$field . $index] = '%' . $or . '%';
              $index++;
            }
          }

          $where = rtrim($where, ' AND ') .  ')'; // was: OR
        }
      }

      // date stuff
      $dateSortWhen = trim($filter['dateWhen']);
      $dateSortNotBefore = trim($filter['dateNotBefore']);
      $dateSortNotAfter = trim($filter['dateNotAfter']);

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

    // SELECT
    $query = $entityManager->createQuery('
      SELECT t, s FROM PapyrillioPapPalBundle:Thumbnail t JOIN t.sample s ' . $where . ' ' .$orderBy
    )->setParameters($parameters);

    $templateVariables = array(
      'thumbnails' => $query->getResult(),
      'filterForm' => $filterForm->createView(),
      'template' => $template,
      'templateOptions' => $templateOptions,
      'sort' => $sort,
      'sortOptions' => $sortOptions,
      'sortDirections' => $sortDirections
    );

    return $this->render('PapyrillioPapPalBundle:Thumbnail:' . $template . '.html.twig', $templateVariables);
  }

}


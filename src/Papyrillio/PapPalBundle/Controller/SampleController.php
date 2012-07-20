<?php

namespace Papyrillio\PapPalBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Papyrillio\PapPalBundle\Entity\Sample;
use Papyrillio\PapPalBundle\Entity\Commet;
use Papyrillio\UserBundle\Entity\User;
use DateTime;
use Date;

class SampleController extends PapPalController{
    
  protected function getFilterForm(){
    $sample = new Sample();

    $form = $this->createFormBuilder($sample)
      ->add('tm', 'text', array('required' => false))
      ->add('hgv', 'text', array('required' => false))
      ->add('ddb', 'text', array('required' => false))
      ->add('dateWhen', 'text', array('required' => false))
      ->add('dateNotBefore', 'text', array('required' => false))
      ->add('dateAfter', 'text', array('required' => false))
      ->add('title', 'text', array('required' => false))
      ->add('material', 'text', array('required' => false))
      ->add('keywords', 'text', array('required' => false))
      ->add('provenance', 'text', array('required' => false))
      ->getForm();

    if ($this->getRequest()->getMethod() == 'POST') {

      $form->bindRequest($this->getRequest());

      // save to session
    } // else retrieve session

    return $form;
  }
  
  protected function getSort(){
    $result = array();
    if($this->getParameter('sort')){
      foreach($this->getParameter('sort') as $sort){
        if(!empty($sort['value'])){
          $result[$sort['value']] = $sort['direction'];
        }
      }
    }
    return $result;
  }

  public function listAction(){
    $template = 'list';
    if($this->container->get('request')->get('_route') == 'PapyrillioPapPalBundle_SampleGallery'){
      $template = 'gallery';
    }
    $filterForm = $this->getFilterForm();
    $sort = $this->getSort();
    $sortOptions = array('' => '', 'tm' => 'TM', 'hgv' => 'HGV', 'ddb' => 'DDB', 'dateSort' => 'Date', 'title' => 'Title', 'material' => 'Material', 'provenance' => 'Provenance', 'status' => 'Status', 'importDate' => 'Import Date');
    $sortDirections = array('asc' => 'ascending', 'desc' => 'descending');
    $filterAnd = array('title');
    $filterOr = array('title', 'tm', 'hgv', 'ddb', 'date', 'material', 'provenance', 'status', 'keywords');

    $entityManager = $this->getDoctrine()->getEntityManager();
    $repository = $entityManager->getRepository('PapyrillioPapPalBundle:Sample');
    
    // ORDER BY

    $orderBy = ' ORDER BY';
    if(count($sort)){
      foreach($sort as $key => $direction){
        if($key == 'hgv'){
          $orderBy .= ' s.tm, s.hgv, ';
        } else {
          $orderBy .= ' s.' . $key . ', ';
        }
      }
      $orderBy = rtrim($orderBy, ', ');
    } else {
      $orderBy .= ' s.dateSort';
    }

    // WHERE

    $where = ' WHERE s.status = :status';
    $parameters = array('status' => 'ok');
    
    if($this->getParameter('form')){
      foreach($this->getParameter('form') as $field => $value){
        if(!empty($value) && in_array($field, $filterOr)){
          $where .= ' AND (';
          $index = 0;
          foreach(explode(' ', $value) as $or){
            $where .= 's.' . $field . ' LIKE :' . $field . $index . ' OR ';
            $parameters[$field . $index] = '%' . $or . '%';
            $index++;
          }

          $where = rtrim($where, ' OR ') .  ')';
        }
      }
    }

    // SELECT
    $query = $entityManager->createQuery('
        SELECT s FROM PapyrillioPapPalBundle:Sample s ' . $where . ' ' . $orderBy .'
      ');
      
    // QUERY

    $query->setParameters($parameters);
    $samples = $query->getResult();

    return $this->render('PapyrillioPapPalBundle:Sample:' . $template . '.html.twig', array('samples' => $samples, 'filterForm' => $filterForm->createView(), 'sort' => $sort, 'sortOptions' => $sortOptions, 'sortDirections' => $sortDirections));

    if ($this->getRequest()->getMethod() == 'POST') {
      
      // REQUEST PARAMETERS
      
      $limit         = $this->getParameter('rows');
      $page          = $this->getParameter('page');
      $offset        = $page * $limit - $limit;
      $offset        = $offset < 0 ? 0 : $offset;
      $sort          = $this->getParameter('sidx');
      $sortDirection = $this->getParameter('sord');
      $visible       = explode(';', rtrim($this->getParameter('visible'), ';'));
      
      // SELECT

      $visibleColumns = array('title');
      foreach($visible as $column){
        if($column != ''){
          $visibleColumns[] = $column;
        }
      }
      $visible = $visibleColumns;

      $this->get('logger')->info('visible: ' . print_r($visible, true));
    

      // WHERE WITH

      if($this->getParameter('_search') == 'true'){
        $prefix = ' WHERE ';

        foreach(array('tm', 'hgv', 'ddb', 'source', 'text', 'position', 'description', 'creator', 'created', 'status') as $field){
          if(strlen($this->getParameter($field))){
            $where .= $prefix . 'c.' . $field . ' LIKE :' . $field;
            $parameters[$field] = '%' . $this->getParameter($field) . '%';
            $prefix = ' AND ';
          }
        }

        if($this->getParameter('edition')){
          $where .= $prefix . '(e.title LIKE :edition OR e.sort LIKE :edition)';
          $parameters['edition'] = '%' . $this->getParameter('edition') . '%';
          $prefix = ' AND ';
        }

        if($this->getParameter('compilation')){
          $where .= $prefix . '(c2.title LIKE :compilation OR c2.volume LIKE :compilation)';
          $parameters['compilation'] = '%' . $this->getParameter('compilation') . '%';
          $prefix = ' AND ';
        }

        $prefix = ' WITH ';
        foreach(array('task_bl', 'task_tm', 'task_hgv', 'task_ddb', 'task_apis', 'task_biblio') as $field){
          if(strlen($this->getParameter($field))){
            $with = $prefix . ' (t.category = \'' . str_replace('task_', '', $field) . '\' AND t.description LIKE \'%' . ($this->getParameter($field) != '*' ? $this->getParameter($field) : '') . '%\')';
            //$key =  ucfirst(str_replace('task_', '', $field));
            //$with = $prefix . ' (t.category = :category' . $key . ' AND t.description LIKE :description' . $key . ')'; 
            //$parameters['category' . $key] = strtolower($field);
            //$parameters['description' . $key] = '%' . $this->getParameter($field) . '%';
            $prefix = ' OR ';
          }
        }
      }

      // LIMIT

      $query = $entityManager->createQuery('
        SELECT count(DISTINCT c.id) FROM PapyrillioBeehiveBundle:Correction c
        LEFT JOIN c.tasks t JOIN c.edition e JOIN c.compilation c2
        ' . $with . ' ' . $where
      );
      $query->setParameters($parameters);
      $count = $query->getSingleScalarResult();
      $totalPages = ($count > 0 && $limit > 0) ? ceil($count/$limit) : 0;
      
      $this->get('logger')->info('limit: ' . $limit);
      $this->get('logger')->info('page: ' . $page);
      $this->get('logger')->info('offset: ' . $offset);
      $this->get('logger')->info('sort: ' . $sort);
      $this->get('logger')->info('sortDirection: ' . $sortDirection);
      $this->get('logger')->info('totalPages: ' . $totalPages);

      // QUERY
      
      $query = $entityManager->createQuery('
        SELECT e, c, t FROM PapyrillioBeehiveBundle:Correction c
        LEFT JOIN c.tasks t JOIN c.edition e JOIN c.compilation c2 ' . $with . ' ' . $where . ' ' . $orderBy
      );
      if(!$print){
        $query->setFirstResult($offset)->setMaxResults($limit);
      }
      $query->setParameters($parameters);

      $corrections = $query->getResult();

      if($print){
        return $this->render('PapyrillioBeehiveBundle:Correction:print.html.twig', array('corrections' => $corrections, 'visible' => $visible));
      } else {
        return $this->render('PapyrillioBeehiveBundle:Correction:list.xml.twig', array('corrections' => $corrections, 'count' => $count, 'totalPages' => $totalPages, 'page' => $page));
      }
    } else {
      if($print){
        return $this->render('PapyrillioBeehiveBundle:Correction:print.html.twig', array('corrections' => $corrections, 'visible' => array()));
      } else {
        return $this->render('PapyrillioBeehiveBundle:Correction:list.html.twig', array('corrections' => $corrections));
      }
    }
  }

  public function showAction($id){
    $entityManager = $this->getDoctrine()->getEntityManager();
    $repository = $entityManager->getRepository('PapyrillioPapPalBundle:Sample');
    $sample = $repository->findOneBy(array('id' => $id));
    
    if(!$sample){
      return $this->forward('PapyrillioPapPalBundle:Sample:list');
    }

    return $this->render('PapyrillioPapPalBundle:Sample:show.html.twig', array('sample' => $sample));
  }

  public function setMasterThumbnailAction($id){
    $entityManager = $this->getDoctrine()->getEntityManager();
    $repository = $entityManager->getRepository('PapyrillioPapPalBundle:Sample');
    $sample = $repository->findOneBy(array('id' => $id));
    $masterThumbnail = $this->getParameter('masterThumbnail');

    if(!empty($masterThumbnail)){
      if($sample and $sample->setMasterThumbnail($masterThumbnail)){
        $this->get('session')->setFlash('notice', 'Preview image has been set as default thumbnail.');
      } else {
        $this->get('session')->setFlash('notice', 'Preview image ' . $masterThumbnail . ' could not bee set as default thumbnail.');
      }
    } else {
      $this->get('session')->setFlash('notice', 'Empty image path');
    }

    return new RedirectResponse($this->generateUrl('PapyrillioPapPalBundle_SampleShow', array('id' => $id)));
  }
}

<?php

namespace Papyrillio\PapPalBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Papyrillio\PapPalBundle\Entity\Sample;
use Papyrillio\PapPalBundle\Entity\Commet;
use Papyrillio\UserBundle\Entity\User;
use DateTime;

class SampleController extends PapPalController{

  public function listAction(){
    $entityManager = $this->getDoctrine()->getEntityManager();
    $repository = $entityManager->getRepository('PapyrillioPapPalBundle:Sample');
    
    $query = $entityManager->createQuery('
        SELECT s FROM PapyrillioPapPalBundle:Sample s ORDER BY s.dateSort
      ');
    
    $samples = $query->getResult();
    
    
    
    return $this->render('PapyrillioPapPalBundle:Sample:list.html.twig', array('samples' => $samples));

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
      $this->get('logger')->info('visible: ' . $this->getParameter('visible'));

      // ODER BY
      
      $orderBy = '';
      if(in_array($sort, array('tm', 'hgv', 'ddb', 'source', 'text', 'position', 'description', 'creator', 'created', 'status'))){
        $orderBy = ' ORDER BY c.' . $sort . ' ' . $sortDirection;
      }
      if($sort == 'edition'){
        $orderBy = ' ORDER BY e.sort, e.title ' . $sortDirection;
      }
      if($sort == 'compilation'){
        $orderBy = ' ORDER BY c2.volume ' . $sortDirection;
      }

      // WHERE WITH
      
      $where = '';
      $with = '';
      $parameters = array();
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
}

<?php

namespace Papyrillio\PapPalBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Papyrillio\PapPalBundle\Entity\Sample;
use Papyrillio\PapPalBundle\Entity\Commet;
use Papyrillio\UserBundle\Entity\User;
use Papyrillio\PapPalBundle\Service\ImagePeer;
use DateTime;
use Date;

class SampleController extends PapPalController{

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
      ->add('keywords', 'text', array('required' => false))
      ->add('provenance', 'text', array('required' => false))
      ->getForm();  // digitalImages, status, importDate

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
  
  public function getTemplate(){
    if($this->getParameter('template')){
      return $this->getParameter('template');
    }

    if($this->container->get('request')->get('_route') == 'PapyrillioPapPalBundle_SampleGallery'){
      return 'gallery';
    }

    return 'list';
  }

  public function listAction(){
    $filterForm = $this->getFilterForm();
    
    $templateOptions = array('list' => 'Gallery', 'gallery' => 'Slideshow');
    $template = $this->getTemplate();
    
    $sort = $this->getSort();
    $sortOptions = array('' => '', 'hgv' => 'HGV', 'ddb' => 'DDB', 'dateSort' => 'Date', 'title' => 'Title', 'material' => 'Material', 'provenance' => 'Provenance', 'status' => 'Status', 'importDate' => 'Import Date');
    $sortDirections = array('asc' => 'ascending', 'desc' => 'descending');
    
    $filterAnd = array('title');
    $filterOr = array('title', 'hgv', 'ddb', 'material', 'provenance', 'keywords', 'status');

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
    
    if($filter = $this->getParameter('form')){
      // standard fields
      foreach($filter as $field => $value){
        $value = trim($value);
        if(!empty($value) && in_array($field, $filterOr)){
          $where .= ' OR ('; /* was: AND */
          $index = 0;
          foreach(explode(' ', $value) as $or){
            $where .= 's.' . $field . ' LIKE :' . $field . $index . ' OR ';
            $parameters[$field . $index] = '%' . $or . '%';
            $index++;
          }

          $where = rtrim($where, ' OR ') .  ')';
        }
      }

      // date stuff
      $dateSortWhen = trim($filter['dateWhen']);
      $dateSortNotBefore = trim($filter['dateNotBefore']);
      $dateSortNotAfter = trim($filter['dateNotAfter']);

      if(!empty($dateSortNotBefore) && !empty($dateSortNotAfter)){
        // between
        
        $dateSortNotBefore = Sample::generateDateSortKey(Sample::makeIsoYear($dateSortNotBefore) . '-01-01');
        $dateSortNotAfter = Sample::generateDateSortKey(Sample::makeIsoYear($dateSortNotAfter) . '-12-31');
        
        $where .= ' AND s.dateSort BETWEEN :dateNotBefore AND :dateNotAfter';
        $parameters['dateNotBefore'] = $dateSortNotBefore;
        $parameters['dateNotAfter'] = $dateSortNotAfter;
      } else if(!empty($dateSortNotBefore)){
        // not before
        
        $dateSortNotBefore = Sample::generateDateSortKey(Sample::makeIsoYear($dateSortNotBefore) . '-01-01');
        
        $where .= ' AND s.dateSort >= :dateNotBefore';
        $parameters['dateNotBefore'] = $dateSortNotBefore;
      } else if(!empty($dateSortNotAfter)){
        // not after
        
        $dateSortNotAfter = Sample::generateDateSortKey(Sample::makeIsoYear($dateSortNotAfter) . '-12-31');
        
        $where .= ' AND s.dateSort <= :dateNotAfter';
        $parameters['dateNotAfter'] = $dateSortNotAfter;
      } else if(!empty($dateSortWhen)){
        $dateSortFrom = Sample::generateDateSortKey(Sample::makeIsoYear($dateSortWhen) . '-01-01');
        $dateSortTo = Sample::generateDateSortKey(Sample::makeIsoYear($dateSortWhen) . '-12-31');
        
        if($dateSortWhen < 0){
          $tmp = $dateSortFrom;
          $dateSortFrom = $dateSortTo;
          $dateSortTo = $tmp;
        }

        $where .= ' AND s.dateSort BETWEEN :dateFrom AND :dateTo';
        $parameters['dateFrom'] = $dateSortFrom;
        $parameters['dateTo'] = $dateSortTo;
      }
      
    }

    // SELECT
    $query = $entityManager->createQuery('
        SELECT s FROM PapyrillioPapPalBundle:Sample s ' . $where . ' ' . $orderBy .'
      ');
      
    // QUERY

    $query->setParameters($parameters);
    $samples = $query->getResult();
    $count = count($samples);

    return $this->render('PapyrillioPapPalBundle:Sample:' . $template . '.html.twig', array('samples' => $samples, 'filterForm' => $filterForm->createView(), 'template' => $template, 'templateOptions' => $templateOptions, 'sort' => $sort, 'sortOptions' => $sortOptions, 'sortDirections' => $sortDirections));

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

  public function tmAction($tm){
    $entityManager = $this->getDoctrine()->getEntityManager();
    $repository = $entityManager->getRepository('PapyrillioPapPalBundle:Sample');

    if($sample = $repository->findOneBy(array('tm' => $tm))){
      return $this->forward('PapyrillioPapPalBundle:Sample:show', array('id' => $sample->getId()));
    }

    return $this->render('PapyrillioPapPalBundle:Sample:notFound.html.twig', array('identifierClass' => 'tm', 'id' => $tm));
  }

  public function hgvAction($hgv){
    $entityManager = $this->getDoctrine()->getEntityManager();
    $repository = $entityManager->getRepository('PapyrillioPapPalBundle:Sample');

    if($sample = $repository->findOneBy(array('hgv' => $hgv))){
      return $this->forward('PapyrillioPapPalBundle:Sample:show', array('id' => $sample->getId()));
    }

    return $this->render('PapyrillioPapPalBundle:Sample:notFound.html.twig', array('identifierClass' => 'hgv', 'id' => $hgv));
  }

  public function ddbAction($ddb){
    $entityManager = $this->getDoctrine()->getEntityManager();
    $repository = $entityManager->getRepository('PapyrillioPapPalBundle:Sample');

    if($sample = $repository->findOneBy(array('ddb' => $ddb))){
      return $this->forward('PapyrillioPapPalBundle:Sample:show', array('id' => $sample->getId()));
    }

    return $this->render('PapyrillioPapPalBundle:Sample:notFound.html.twig', array('identifierClass' => 'ddb', 'id' => $ddb));
  }

  public function showAction($id){
    $entityManager = $this->getDoctrine()->getEntityManager();
    $repository = $entityManager->getRepository('PapyrillioPapPalBundle:Sample');
    $sample = $repository->findOneBy(array('id' => $id));
    
    if(!$sample){
      return $this->forward('PapyrillioPapPalBundle:Sample:list');
    }

    return $this->render('PapyrillioPapPalBundle:Sample:show.html.twig', array('sample' => $sample, 'uploadForm' => $this->getUploadForm()->createView(), 'clockwise' => ImagePeer::DIRECTION_CLOCKWISE, 'counterclockwise' => 90));
  }

  public function setMasterThumbnailAction($id){
    $entityManager = $this->getDoctrine()->getEntityManager();
    $repository = $entityManager->getRepository('PapyrillioPapPalBundle:Sample');
    
    $masterThumbnail = $this->getParameter('masterThumbnail');

    if(!empty($masterThumbnail)){
      if($sample = $repository->findOneBy(array('id' => $id))){
        if($sample->setMasterThumbnail($masterThumbnail)){
          $this->get('session')->setFlash('notice', 'Preview image has been set as default thumbnail.');
        } else {
          $this->get('session')->setFlash('error', 'Preview image ' . $masterThumbnail . ' could not bee set as default thumbnail.');
        }
      } else {
        $this->get('session')->setFlash('error', 'Preview image ' . $masterThumbnail . ' could not bee set becaus record #' . $id . ' does not exist.');        
      }
      
    } else {
      $this->get('session')->setFlash('error', 'Empty image path');
    }

    return new RedirectResponse($this->generateUrl('PapyrillioPapPalBundle_SampleShow', array('id' => $id)));
  }

  public function deleteAction($id){
    $entityManager = $this->getDoctrine()->getEntityManager();
    $repository = $entityManager->getRepository('PapyrillioPapPalBundle:Sample');
    $sample = $repository->findOneBy(array('id' => $id));

    if($sample){
      $entityManager->remove($sample);
      $entityManager->flush();
      $this->get('session')->setFlash('notice', 'Data record was deleted.');
      return new RedirectResponse($this->generateUrl('PapyrillioPapPalBundle_SampleList'));
    }

    return new RedirectResponse($this->generateUrl('PapyrillioPapPalBundle_SampleShow', array('id' => $id)));
  }

  protected function getUploadForm(){
    $uploadForm = $this->get('form.factory')
     ->createBuilder('form')
     ->add('image','file', array('required' => true))
     ->getForm();
    
    if($this->get('request')->getMethod() == 'POST'){
      $uploadForm->bindRequest($this->get('request'));
    }

    return $uploadForm;
  }

  public function deleteImageAction($id, $image){
    if($sample = $this->getSample($id)){
      if(file_exists($filepath = $this->getFilepathForImage($sample, $image))){
          if(unlink($filepath)){
            return new Response(json_encode(array('success' => true, 'data' => array('id' => $id, 'image' => $image))));          
          } else {
            return new Response(json_encode(array('success' => false, 'error' => 'File ' . $filepath . ' could not be deleted.')));
          }
      } else {
        return new Response(json_encode(array('success' => false, 'error' => 'File ' . $filepath . ' could not be found on this system.')));
      }
    } else {
      return new Response(json_encode(array('success' => false, 'error' => 'Sample record #' . $id . ' could not be found.')));
    }
  }

  public function uploadImageAction($id){
    if($sample = $this->getSample($id)){
      if($this->get('request')->getMethod() == 'POST'){
        $uploadForm = $this->getUploadForm();
        if($uploadForm->isValid()){
          //Symfony\Component\HttpFoundation\File\UploadedFile
          $files = $this->get('request')->files->get($uploadForm->getName());
          $uploadedFile = $files['image'];
          if($uploadedFile->getMimeType() == 'image/jpeg'){

            $imageDirectory = $this->makeSureImageDirectoryExists($sample);

            // make sure it ends with ».jpg«
            $filename = $uploadedFile->getClientOriginalName();
            $match = array();
            if(preg_match('/^(.+)\.jpe?g$/i', $filename, $match)){
              $filename = $match[1] . '.jpg';
            } else {
              $filename .= '.jpg';
            }

            // make sure there is no file by this name already
            $i = 0;
            $targetFile = $imageDirectory . '/' . $filename;
            while(file_exists($targetFile)){
              $indexedFilename = substr($filename, 0, strrpos($filename, '.')) . '_' . ++$i . '.jpg';
              $targetFile = $imageDirectory . '/' . $indexedFilename;
            }
            $filename = $i ? $indexedFilename : $filename;

            // move file
            $uploadedFile->move($imageDirectory, $filename);

            // create thumbnails
            $thumbnailDirectory = $this->getDirectoryForThumbnails($sample);
            $cropper = $this->get('papyrillio_pap_pal.image_cropper');
            $cropper->crop($imageDirectory, $filename, $thumbnailDirectory, $sample->getHgv());

            $this->get('session')->setFlash('notice', 'Image has been uploaded.');
          } else {
            $this->get('session')->setFlash('error', 'Mime type ' . $uploadedFile->getMimeType() . ' not accepted. Please upload only jpg images.');
          }
        } else {
          $this->get('session')->setFlash('error', 'Invalid form data.');
        }
      }
      return new RedirectResponse($this->generateUrl('PapyrillioPapPalBundle_SampleShow', array('id' => $id)));
    } else {
      return new RedirectResponse($this->generateUrl('PapyrillioPapPalBundle_SampleList'));
    }
  }

  public function deleteThumbnailAction($id, $thumbnail){
    if($sample = $this->getSample($id)){
      if(file_exists($filepath = $this->getFilepathForThumbnail($sample, $thumbnail))){
        $link = readlink($sample->getThumbnail(true));
        if(strstr($link, '/' . $thumbnail) === FALSE){
          if(unlink($filepath)){
            return new Response(json_encode(array('success' => true, 'data' => array('id' => $id, 'thumbnail' => $thumbnail))));          
          } else {
            return new Response(json_encode(array('success' => false, 'error' => 'File ' . $filepath . ' could not be deleted.')));
          }
        } else {
          return new Response(json_encode(array('success' => false, 'error' => 'Thumbnail ' . $thumbnail . ' could be deleted because it is the current preview item.')));
        }
      } else {
        return new Response(json_encode(array('success' => false, 'error' => 'File ' . $filepath . ' could not be found on this system.')));
      }
    } else {
      return new Response(json_encode(array('success' => false, 'error' => 'Sample record #' . $id . ' could not be found.')));
    }
  }

  public function rotateThumbnailAction($id, $thumbnail, $direction){
    if($sample = $this->getSample($id)){
      $thumbnailDirectory = $this->get('kernel')->getRootDir() . '/../web/thumbnail';
      $folderDirectory = $thumbnailDirectory . '/' . $sample->getFolder();
      $hgvDirectory = $folderDirectory . '/' . $sample->getHgv();
      $filepath =  $hgvDirectory . '/' . $thumbnail;

      if(file_exists($filepath)){
        try{
          $rotator = $this->get('papyrillio_pap_pal.image_rotator');
          $rotator->rotate($hgvDirectory, $thumbnail, $direction);
          return new Response(json_encode(array('success' => true, 'data' => array('id' => $id, 'thumbnail' => $thumbnail))));          
        } catch(Exception $e) {
          return new Response(json_encode(array('success' => false, 'error' => 'File ' . $filepath . ' could not be rotated (' . $e->getMessage() . ').')));
        }
      } else {
        return new Response(json_encode(array('success' => false, 'error' => 'File ' . $filepath . ' could not be found on this system.')));
      }
    }
  }

}

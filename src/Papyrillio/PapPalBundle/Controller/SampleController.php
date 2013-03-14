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
    
    if($this->container->get('request')->get('_route') == 'PapyrillioPapPalBundle_SampleGallery'){

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
    $repository = $entityManager->getRepository('PapyrillioPapPalBundle:Sample');

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
        SELECT s FROM PapyrillioPapPalBundle:Sample s ' . $where . ' ' . $orderBy .'
      ');

    // QUERY

    $query->setParameters($parameters);
    $samples = $query->getResult();
    $count = count($samples);

    return $this->render('PapyrillioPapPalBundle:Sample:' . $template . '.html.twig', array('samples' => $samples, 'filterForm' => $filterForm->createView(), 'template' => $template, 'templateOptions' => $templateOptions, 'sort' => $sort, 'sortOptions' => $sortOptions, 'sortDirections' => $sortDirections));
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

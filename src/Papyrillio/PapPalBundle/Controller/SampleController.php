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

class SampleController extends PapPalController{

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

    return $this->render('PapyrillioPapPalBundle:Sample:show.html.twig', array('sample' => $sample, 'uploadForm' => $this->getUploadForm()->createView(), 'clockwise' => ImagePeer::DIRECTION_CLOCKWISE, 'counterclockwise' => ImagePeer::DIRECTION_COUNTERCLOCKWISE));
  }

  public function deleteAction($id){
    $entityManager = $this->getDoctrine()->getEntityManager();
    $repository = $entityManager->getRepository('PapyrillioPapPalBundle:Sample');
    $sample = $repository->findOneBy(array('id' => $id));

    if($sample){
      $entityManager->remove($sample);
      $entityManager->flush();
      $this->get('session')->setFlash('notice', 'Data record was deleted.');
      return new RedirectResponse($this->generateUrl('PapyrillioPapPalBundle_ThumbnailList'));
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
      return new RedirectResponse($this->generateUrl('PapyrillioPapPalBundle_ThumbnailList'));
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

  public function setMasterThumbnailAction($id, $thumbnail, $language = 'grc'){
    $entityManager = $this->getDoctrine()->getEntityManager();
    $repository = $entityManager->getRepository('PapyrillioPapPalBundle:Sample');
    $error = '';

    if(!empty($thumbnail)){
      if($sample = $this->getSample($id)){
        if($sample->setMasterThumbnail($thumbnail, $language)){
          if(!$sample->getThumbnailByLanguage($language)){
            $newThumbnail = new Thumbnail($language);
            $newThumbnail->setSample($sample);
            $entityManager->persist($newThumbnail);
            $entityManager->flush();
            $sample->addThumbnail($newThumbnail); // Nanyatte!?!
          }
          return new Response(json_encode(array('success' => true, 'data' => array('id' => $id, 'thumbnail' => $thumbnail, 'asset' => $sample->getThumbnailByLanguage($language)->getFile(), 'language' => $language))));
        } else {
          $error = 'Preview image ' . $thumbnail . ' could not bee set as default thumbnail.';
        }
      } else {
        $error = 'Preview image ' . $thumbnail . ' could not bee set because record #' . $id . ' does not exist.';
      }
    } else {
      $error = 'Empty image path';
    }

    return new Response(json_encode(array('success' => false, 'error' => $error)));
  }

  public function unsetMasterThumbnailAction($id){
    $entityManager = $this->getDoctrine()->getEntityManager();
    $repository = $entityManager->getRepository('PapyrillioPapPalBundle:Sample');
    $language = $this->getParameter('language');
    $languages = array('lat' => 'Lateinisch', 'grc' => 'Griechisch');

    if($sample = $this->getSample($id)){
      if($sample->unsetMasterThumbnail($language)){
        if($thumbnail = $sample->getThumbnailByLanguage($language)){
          $entityManager->remove($thumbnail);
          $entityManager->flush();
          $this->get('session')->setFlash('notice', 'Master thumbnail for language ' . $languages[$language] . ' has been deleted.');
        } else {
          $this->get('session')->setFlash('error', 'Master thumbnail could not be deleted from database.');
        }
      } else {
        $this->get('session')->setFlash('error', 'Master thumbnail could not be unset because symbolic link could not be deleted from filesystem.');
      }
    } else {
      $this->get('session')->setFlash('error', 'Master thumbnail could not be unset because sample record #' . $id . ' does not exist.');
    }

    return new RedirectResponse($this->generateUrl('PapyrillioPapPalBundle_SampleShow', array('id' => $id)));
  }

}


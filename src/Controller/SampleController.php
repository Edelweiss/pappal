<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Sample;
use App\Entity\Comment;
use App\Entity\Thumbnail;
use App\Entity\User;
use App\Form\Type\SampleImageType;

use App\Service\ImageRotator;
use App\Service\ImageCropper;
use App\Service\ImagePeer;
use DateTime;
use Date;

class SampleController extends PapPalController{

  public function tm($tm): Response{
    $entityManager = $this->getDoctrine()->getManager();
    $repository = $entityManager->getRepository(Sample::class);

    if($sample = $repository->findOneBy(array('tm' => $tm))){
      return $this->forward('sample/show', array('id' => $sample->getId(), '_route' => $this->getRequest()->attributes->get('_route')));
    }

    return $this->render('sample/notFound.html.twig', array('identifierClass' => 'tm', 'id' => $tm));
  }

  public function hgv($hgv): Response {
    $entityManager = $this->getDoctrine()->getManager();
    $repository = $entityManager->getRepository(Sample::class);

    if($sample = $repository->findOneBy(array('hgv' => $hgv))){
      return $this->forward('sample/show', array('id' => $sample->getId(), '_route' => $this->getRoute()));
    }

    return $this->render('sample/notFound.html.twig', array('identifierClass' => 'hgv', 'id' => $hgv));
  }

  public function ddb($ddb): Response {
    $entityManager = $this->getDoctrine()->getManager();
    $repository = $entityManager->getRepository(Sample::class);

    if($sample = $repository->findOneBy(array('ddb' => $ddb))){
      return $this->forward('sample/show', array('id' => $sample->getId(), '_route' => $this->getRequest()->attributes->get('_route')));
    }

    return $this->render('sample/notFound.html.twig', array('identifierClass' => 'ddb', 'id' => $ddb));
  }

  public function show($id): Response {
    $entityManager = $this->getDoctrine()->getManager();
    $repository = $entityManager->getRepository(Sample::class);
    $sample = $repository->findOneBy(array('id' => $id));

    if(!$sample){
      return $this->forward('sample/list');
    }

    return $this->render('sample/show.html.twig', array('sample' => $sample, 'uploadForm' => $this->getUploadForm()->createView(), 'clockwise' => ImagePeer::DIRECTION_CLOCKWISE, 'counterclockwise' => ImagePeer::DIRECTION_COUNTERCLOCKWISE));
  }

  public function delete($id): Response {
    $entityManager = $this->getDoctrine()->getManager();
    $repository = $entityManager->getRepository(Sample::class);
    $sample = $repository->findOneBy(array('id' => $id));

    if($sample){
      foreach($sample->getThumbnails() as $thumbnail){
        $entityManager->remove($thumbnail);
      }
      $entityManager->remove($sample);
      $entityManager->flush();
      $this->addFlash('notice', 'Data record was deleted.');
      return new RedirectResponse($this->generateUrl('PapyrillioPapPalBundle_ThumbnailList'));
    }

    return new RedirectResponse($this->generateUrl('PapyrillioPapPalBundle_SampleShow', array('id' => $id)));
  }

  protected function getUploadForm(){
    $uploadForm = $this->createForm(SampleImageType::class, new Sample());

    if($this->request->getMethod() == 'POST'){
      $uploadForm->handleRequest($this->request);
    }

    return $uploadForm;
  }

  public function deleteImage($id, $image): Response {
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

  public function uploadImage($id, ImageCropper $cropper): Response {
    if($sample = $this->getSample($id)){
      if($this->request->getMethod() == 'POST'){
        $uploadForm = $this->getUploadForm();
        if($uploadForm->isValid()){
          //Symfony\Component\HttpFoundation\File\UploadedFile
          $files = $this->request->files->get($uploadForm->getName());
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
            $cropper->crop($imageDirectory, $filename, $thumbnailDirectory, $sample->getHgv());

            $this->addFlash('notice', 'Image has been uploaded.');
          } else {
            $this->addFlash('error', 'Mime type ' . $uploadedFile->getMimeType() . ' not accepted. Please upload only jpg images.');
          }
        } else {
          $this->addFlash('error', 'Invalid form data.');
        }
      }
      return new RedirectResponse($this->generateUrl('PapyrillioPapPalBundle_SampleShow', array('id' => $id)));
    } else {
      return new RedirectResponse($this->generateUrl('PapyrillioPapPalBundle_ThumbnailList'));
    }
  }

  public function deleteThumbnail($id, $thumbnail): Response {
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

  public function rotateThumbnail($id, $thumbnail, $direction, ImageRotator $rotator): Response {
    if($sample = $this->getSample($id)){
      $thumbnailDirectory = $this->getParameter('kernel.project_dir'). '/public/thumbnail';
      $folderDirectory = $thumbnailDirectory . '/' . $sample->getFolder();
      $hgvDirectory = $folderDirectory . '/' . $sample->getHgv();
      $filepath =  $hgvDirectory . '/' . $thumbnail;

      if(file_exists($filepath)){
        try{
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

  public function setMasterThumbnail($id, $thumbnail, $language = 'grc'): Response {
    $entityManager = $this->getDoctrine()->getManager();
    $repository = $entityManager->getRepository(Sample::class);
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

  public function unsetMasterThumbnail($id): Response {
    $entityManager = $this->getDoctrine()->getManager();
    $repository = $entityManager->getRepository(Sample::class);
    $language = $this->getParameter('language');
    $languages = array('lat' => 'Lateinisch', 'grc' => 'Griechisch');

    if($sample = $this->getSample($id)){
      if($sample->unsetMasterThumbnail($language)){
        if($thumbnail = $sample->getThumbnailByLanguage($language)){
          $entityManager->remove($thumbnail);
          $entityManager->flush();
          $this->addFlash('notice', 'Master thumbnail for language ' . $languages[$language] . ' has been deleted.');
        } else {
          $this->addFlash('error', 'Master thumbnail could not be deleted from database.');
        }
      } else {
        $this->addFlash('error', 'Master thumbnail could not be unset because symbolic link could not be deleted from filesystem.');
      }
    } else {
      $this->addFlash('error', 'Master thumbnail could not be unset because sample record #' . $id . ' does not exist.');
    }

    return new RedirectResponse($this->generateUrl('PapyrillioPapPalBundle_SampleShow', array('id' => $id)));
  }

}
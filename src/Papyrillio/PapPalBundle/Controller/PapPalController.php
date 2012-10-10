<?php

namespace Papyrillio\PapPalBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class PapPalController extends Controller{
  protected function getParameter($key){
    $get  = $this->getRequest()->query->get($key);
    $post = $this->getRequest()->request->get($key);
    
    if($post && (is_array($post) || strlen(trim($post)))){
      return $post;
    }

    return $get;
  }

  protected function getSample($id){
    $entityManager = $this->getDoctrine()->getEntityManager();
    $repository = $entityManager->getRepository('PapyrillioPapPalBundle:Sample');
    return $repository->findOneBy(array('id' => $id));
  }

  // getFilepath

  protected function getFilepathForImage($sample, $image){
    return $this->getDirectoryForImages($sample) . '/' . $image;
  }

  protected function getFilepathForThumbnail($sample, $thumbnail){
    return $this->getDirectoryForThumbnails($sample) . '/' . $thumbnail;
  }
  
  // getDirectory

  protected function getDirectoryForImages($sample){
    return $this->getDirectory($sample, 'sample');
  }

  protected function getDirectoryForThumbnails($sample){
    return $this->getDirectory($sample, 'thumbnail');
  }

  protected function getDirectory($sample, $rootDirectory = 'sample'){
    return $this->get('kernel')->getRootDir() . '/../web/' . $rootDirectory . '/' . $sample->getFolder() . '/' . $sample->getHgv();
  }

  // makeSureDirectoryExists

  protected function makeSureImageDirectoryExists($sample){
    return $this->makeSureDirectoryExists($sample, 'sample');
  }

  protected function makeSureThumbnailDirectoryExists($sample){
    return $this->makeSureDirectoryExists($sample, 'thumbnail');
  }

  protected function makeSureDirectoryExists($sample, $rootDirectory = 'sample'){
    $rootDirectory = $this->get('kernel')->getRootDir() . '/../web/' . $rootDirectory;
    $folderDirectory = $rootDirectory . '/' . $sample->getFolder();
    $hgvDirectory = $folderDirectory . '/' . $sample->getHgv();

    if(!file_exists($folderDirectory)){
      mkdir($folderDirectory);
    }
    if(!file_exists($hgvDirectory)){
      mkdir($hgvDirectory);
    }

    return $hgvDirectory;
  }
  
  

}

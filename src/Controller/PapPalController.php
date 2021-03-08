<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class PapPalController extends AbstractController{
  protected $request;
  protected $session;

  public function __construct(RequestStack $requestStack, SessionInterface $session)
  {
      $this->request = $requestStack->getCurrentRequest();
      $this->session = $session;
  }

  protected function getParameter($key){
    return $this->request->query->all($key);
  }

  protected function getRoute(){
    return $this->request->attributes->get('_route');
  }

  protected function getMemo(){
    if($this->session->get('memo')){
      return $this->session->get('memo'); // retrieve from session
    }
    return array();
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

<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Sample;
use App\Entity\Comment;
use App\Entity\User;


#use Symfony\Component\HttpFoundation\RedirectResponse;

#use Papyrillio\PapPalBundle\Service\ImagePeer;
use DateTime;
use Date;

class ImageController extends PapPalController{

  public function crop($id, $image): Response {
    if($sample = $this->getSample($id)){
      if(file_exists($this->getFilepathForImage($sample, $image))){

        if($this->getRequest()->getMethod() === 'POST'){

          $coordinates = $this->getParameter('image');
          $cropper = $this->get('papyrillio_pap_pal.image_cropper');

          try{
            $cropper->cropSpecial($coordinates['x'], $coordinates['y'], $coordinates['w'], $coordinates['h'], $this->getDirectoryForImages($sample), $image, $this->makeSureThumbnailDirectoryExists($sample), $sample->getHgv());
          }catch(Exception $e){
            $this->get('session')->setFlash('notice', 'File ' . $filepath . ' could not be cropped.');
            return new RedirectResponse($this->generateUrl('PapyrillioPapPalBundle_SampleShow', array('id' => $id)));
          }

          return new RedirectResponse($this->generateUrl('PapyrillioPapPalBundle_SampleShow', array('id' => $id)));
        }
      } else {
        $this->get('session')->setFlash('notice', 'File ' . $filepath . ' could not be found on this system.');
        return new RedirectResponse($this->generateUrl('PapyrillioPapPalBundle_SampleShow', array('id' => $id)));
      }
    } else {
      $this->get('session')->setFlash('notice', 'Sample record #' . $id . ' could not be found.');
        return new RedirectResponse($this->generateUrl('PapyrillioPapPalBundle_ThumbnailGallery'));
    }
    return $this->render('PapyrillioPapPalBundle:Image:crop.html.twig', array('sample' => $sample, 'image' => $image));
  }
}

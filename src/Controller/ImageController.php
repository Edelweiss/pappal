<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Service\ImageCropper;
use App\Entity\Sample;
use App\Entity\Comment;
use App\Entity\User;


#use Symfony\Component\HttpFoundation\RedirectResponse;

#use Papyrillio\PapPalBundle\Service\ImagePeer;
use DateTime;
use Date;

class ImageController extends PapPalController{

  public function crop($id, $image, ImageCropper $cropper): Response {
    if($sample = $this->getSample($id)){
      if(file_exists($this->getFilepathForImage($sample, $image))){

        if($this->getRequest()->getMethod() === 'POST'){

          $coordinates = $this->getParameter('image');

          try{
            $cropper->cropSpecial($coordinates['x'], $coordinates['y'], $coordinates['w'], $coordinates['h'], $this->getDirectoryForImages($sample), $image, $this->makeSureThumbnailDirectoryExists($sample), $sample->getHgv());
          }catch(Exception $e){
            $this->addFlash('notice', 'File ' . $filepath . ' could not be cropped.');
            return new RedirectResponse($this->generateUrl('PapyrillioPapPalBundle_SampleShow', array('id' => $id)));
          }

          return new RedirectResponse($this->generateUrl('PapyrillioPapPalBundle_SampleShow', array('id' => $id)));
        }
      } else {
        $this->addFlash('notice', 'File ' . $filepath . ' could not be found on this system.');
        return new RedirectResponse($this->generateUrl('PapyrillioPapPalBundle_SampleShow', array('id' => $id)));
      }
    } else {
      $this->addFlash('notice', 'Sample record #' . $id . ' could not be found.');
        return new RedirectResponse($this->generateUrl('PapyrillioPapPalBundle_ThumbnailGallery'));
    }
    return $this->render('image/crop.html.twig', array('sample' => $sample, 'image' => $image));
  }
}

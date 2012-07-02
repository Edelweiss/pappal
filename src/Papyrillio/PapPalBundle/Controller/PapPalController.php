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
}

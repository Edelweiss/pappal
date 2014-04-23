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

class MemoController extends PapPalController{
  public function defaultAction(){
    return $this->render('PapyrillioPapPalBundle:Memo:default.html.twig', array('sampleList' => $this->getMemo()));
  }
  
  public function addAction($id){
    $memo = $this->getMemo();
    if(!in_array($id, $memo)){
      $memo[] = $id;
    }
    $this->setMemo($memo);
    return new Response(json_encode(array('success' => true, 'data' => array('id' => $id, 'memo' => $memo))));
  }

  public function removeAction($id){
    $memo = $this->getMemo();
    if(in_array($id, $memo)){
      unset($memo[array_search($id, $memo)]);
    }
    $this->setMemo($memo);
    return new Response(json_encode(array('success' => true, 'data' => array('id' => $id, 'memo' => $memo))));
  }

  protected function setMemo($memo){
    $this->getRequest()->getSession()->set('memo', $memo); // save to session
  }
}


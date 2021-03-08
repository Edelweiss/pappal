<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Sample;
use App\Entity\Comment;
use App\Entity\Thumbnail;
use App\Entity\User;

#use Papyrillio\PapPalBundle\Service\ImagePeer;
use DateTime;
use Date;

class MemoController extends PapPalController{
  public function default(): Response{
    $thumbnailList = array();
    if(count($this->getMemo())){
      $where = ' WHERE s.status = ?0 AND ';
      $parameters = array(0 => 'ok');
      $index = 1;
      foreach ($this->getMemo() as $thumbnailId) {
        $where .= ' t.id = ?' . $index . ' OR';
        $parameters[$index++] = $thumbnailId;
      }
      $where = rtrim($where, ' OR') . '';

      $entityManager = $this->getDoctrine()->getEntityManager();
      $repository = $entityManager->getRepository('PapyrillioPapPalBundle:Thumbnail');

      $query = $entityManager->createQuery('
        SELECT t, s FROM PapyrillioPapPalBundle:Thumbnail t JOIN t.sample s' . $where
      )->setParameters($parameters);

      $thumbnailList = $query->getResult();
    }

    return $this->render('memo/default.html.twig', ['thumbnailList' => $thumbnailList]);
  }
  
  public function add($id): Response {
    $memo = $this->getMemo();
    if(!in_array($id, $memo)){
      $memo[] = $id;
    }
    $this->setMemo($memo);
    return new Response(json_encode(array('success' => true, 'data' => array('id' => $id, 'memo' => $memo))));
  }

  public function remove($id): Response {
    $memo = $this->getMemo();
    if(in_array($id, $memo)){
      unset($memo[array_search($id, $memo)]);
    }
    $this->setMemo($memo);
    return new Response(json_encode(array('success' => true, 'data' => array('id' => $id, 'memo' => $memo))));
  }

  public function clear(): Response {
    $this->setMemo(array());
    return new RedirectResponse($this->generateUrl('PapyrillioPapPalBundle_Memo'));
  }

  protected function setMemo($memo){
    $this->session->set('memo', $memo); // save to session
  }
}


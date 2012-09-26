<?php

namespace Papyrillio\PapPalBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('PapyrillioPapPalBundle:Default:index.html.twig', array('name' => $name));
    }

    public function homeAction()
    {
        return $this->render('PapyrillioPapPalBundle:Default:home.html.twig');
    }
}

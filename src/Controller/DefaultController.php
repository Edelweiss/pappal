<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends PapPalController
{
    public function index($name): Response
    {
        return $this->render('default/index.html.twig', ['name' => $name]);
    }

    public function home(): Response
    {
        return $this->render('default/home.html.twig');
    }

    public function about(): Response
    {
        return $this->render('default/about.html.twig');
    }

    public function browsingTips(): Response
    {
        return $this->render('default/browsingTips.html.twig');
    }

    public function contact(): Response
    {
        return $this->render('default/contact.html.twig');
    }

    public function hgvChanges($jourFixe = null): Response
    {
        $year  = date('Y', time());
        $month = date('n', time());
        $day   = date('j', time());
        $hgvChanges = '<p>Ask James!</p>';

        if($jourFixe){
           $command = 'java -Xms512m -Xmx1536m net.sf.saxon.Query -q:/Users/Admin/xwalk/trunk/getLatestHgvChanges.xql jourFixe=' . $jourFixe;
           $command = 'ls -lisaH /Users/Admin/xwalk/trunk/getLatestHgvChanges.xql';
           $command = 'whoami';
           $command = '/usr/bin/java -version';
           $splinters = explode('-', $jourFixe);
           $year  = $splinters[0];
           $month = $splinters[1];
           $day   = $splinters[2];
           $hgvChanges = system($command);
           
           if($hgvChanges){
             $hgvChanges .= 'mui mui: ' . print_r($hgvChanges);
           } else {
             $hgvChanges .= 'bui bui: ' . print_r($hgvChanges);
           }
        }

        return $this->render('default/hgvChanges.html.twig', ['year' => $year, 'month' => $month, 'day' => $day, 'hgvChanges' => $hgvChanges]);
    }
}

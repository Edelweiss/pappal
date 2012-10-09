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

    public function contactAction()
    {
        return $this->render('PapyrillioPapPalBundle:Default:contact.html.twig');
    }

    public function hgvChangesAction($jourFixe = null)
    {
        $year = date('Y', time());
        $month = date('n', time());
        $day = date('j', time());
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

        return $this->render('PapyrillioPapPalBundle:Default:hgvChanges.html.twig', array('year' => $year, 'month' => $month, 'day' => $day, 'hgvChanges' => $hgvChanges));
    }
}

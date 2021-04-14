<?php

/*
(1)
rsync -av Admin@147.142.225.252:/Users/Admin/PapPal/images/thumbnail ./

(2)
change $path in 'first' code line

(3)
run script> php fileLinker.php
*/

$path = '/Users/elemmire/Papy_HCCH/data/pappal/thumbnail';

$directory = new \RecursiveDirectoryIterator($path);
$iterator = new \RecursiveIteratorIterator($directory);
$files = array();
foreach($iterator as $info){
  if(preg_match('/^\d+[a-z]*\.jpg$/', $info->getFilename(), )){
    $files[] = $info->getPathname();
  }
}

foreach($files as $link){
    //var_dump($file);
    $linkTarget = preg_replace('#^.+/(\d+[a-z]*_\d+_\d+\.jpg)$#', '\1', readlink($link));
    echo "__\n";
    echo $link . "\n";
    echo $linkTarget . "\n";
    unlink($link);
    symlink($linkTarget, $link);
}

<?php

namespace Papyrillio\PapPalBundle\Service;

class RegExp
{
    function __construct(){

    }
    
    /*public static function search($string, $pattern){
      mb_regex_encoding("UTF-8");
      mb_ereg_search_init($string, $pattern);
      if(mb_ereg_search()){
        $resultList = array(mb_ereg_search_getregs());
        while($result = mb_ereg_search_regs()){
          $resultList[] = $result;
        }
        return count($resultList) === 1 ? $resultList[0] : $resultList;
      }
      return false;
    }*/
    
    public static function search($string, $pattern, $option = null){
      mb_regex_encoding("UTF-8");
      
      if($option){
        mb_ereg_search_init($string, $pattern, $option);
      } else {
        mb_ereg_search_init($string, $pattern);
      }

      if(mb_ereg_search()){
        return mb_ereg_search_getregs();
      }
      return false;
    }
    
    public static function searchAll($string, $pattern){
      mb_regex_encoding("UTF-8");
      mb_ereg_search_init($string, $pattern);
      if(mb_ereg_search()){
        $resultList = array(mb_ereg_search_getregs());
        while($result = mb_ereg_search_regs()){
          $resultList[] = $result;
        }
        return $resultList;
      }
      return false;
    }
    
    
    public static function replace($string, $pattern, $replacement){
      return mb_ereg_replace($pattern, $replacement, $string);
    }
}

?>

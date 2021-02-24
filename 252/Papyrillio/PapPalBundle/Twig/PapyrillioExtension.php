<?php

namespace Papyrillio\PapPalBundle\Twig;

class PapyrillioExtension extends \Twig_Extension
{
  public function getFilters()
  {
    return array(
      'iso' => new \Twig_Filter_Method($this, 'iso'),
    );
  }
  
  function getFunctions()
   {
    return array(
        'processTranslations' => new \Twig_Function_Method($this, 'processTranslations')
    );
   }

  public function iso($value)
  {
    $iso = array(
      'grc' => 'Griechisch',
      'lat' => 'Lateinisch',
      'cop' => 'Koptisch',
      'egy' => 'Demotisch',
      'ara' => 'Arabisch',
      'Griechisch' => 'grc',
      'Lateinisch' => 'lat',
      'Koptisch'   => 'cop',
      'Demotisch'  => 'egy',
      'Arabisch'   => 'ara'
    );
    if(array_key_exists($value, $iso)) {
      return $iso[$value];
    }
    return $value;
  }

  public function processTranslations($input)
  {
    if(preg_match_all('/(([^: ]+): )([^:]+([ \.$\d]|$))/', $input, $matches)){
      return '<i>italic</i>';
    } else {
      return $input;
    }
  }

  public function getName()
  {
    return 'papyrillio_extension';
  }
}
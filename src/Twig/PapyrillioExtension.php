<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class PapyrillioExtension extends AbstractExtension {
  public function getFilters() {
    return [
      new TwigFilter('iso', [$this, 'iso']),
    ];
  }

  public function getFunctions() {
    return [
      new TwigFunction('processTranslations', [$this, 'processTranslations']),
    ];
   }

  public function iso($value) {
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

}
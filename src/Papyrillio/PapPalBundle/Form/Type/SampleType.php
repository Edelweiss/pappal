<?php

namespace Papyrillio\PapPalBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class SampleType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
      $builder->add('hgv', 'text', array('required' => false)); // e.g. 1915a
      $builder->add('ddb', 'text', array('required' => false)); // e.g. bgu
      $builder->add('dateWhen', 'text', array('required' => false)); 
      $builder->add('dateNotBefore', 'text', array('required' => false));
      $builder->add('dateNotAfter', 'text', array('required' => false));
      $builder->add('title', 'text', array('required' => false));
      $builder->add('material', 'choice', array('choices' => array('Papyrus' => 'Papyrus', 'Ostrakon' => 'Ostrakon', 'tafel' => 'Tafeln'), 'preferred_choices' => array(''), 'required' => false));
      $builder->add('keywords', 'text', array('required' => false));
      $builder->add('provenance', 'text', array('required' => false));
    }
    
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Papyrillio\PapPalBundle\Entity\Sample',
        );
    }

    public function getName()
    {
        return 'sample';
    }
}

<?php

namespace Papyrillio\PapPalBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class ThumbnailType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('sample', new SampleType());
        $builder->add('language', 'choice', array('choices' => array('grc' => 'Griechisch', 'lat' => 'Lateinisch'), 'preferred_choices' => array(''), 'required' => false));
        //$builder->add('template', null, array('property_path' => false));
    }
    
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Papyrillio\PapPalBundle\Entity\Thumbnail',
        );
    }

    public function getName()
    {
        return 'thumbnail';
    }
}

<?php
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Form\Type\SampleType;
use App\Entity\Thumbnail;

class ThumbnailType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options): void {
        $builder->add('sample', SampleType::class);
        $builder->add('language', ChoiceType::Class, ['choices' => ['Griechisch' => 'grc', 'Lateinisch' => 'lat', 'Koptisch' => 'cop'],
                                                      'preferred_choices' => array(''),
                                                      'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void {
        $resolver->setDefaults([
            'data_class' => Thumbnail::class,
        ]);
    }
}

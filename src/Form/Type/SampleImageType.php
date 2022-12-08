<?php
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use App\Entity\Sample;

class SampleImageType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options): void {
      $builder->add('image', FileType::class, [
        'label' => 'Upload image',
        'mapped' => false,
        'required' => true,
        'constraints' => [
           new File([
              'mimeTypes' => [
                  'image/jpeg', 'image/png'
                  ],
           'mimeTypesMessage' => 'Please upload jpg or png file.'
           ])
        ]
      ]);
      $builder->add('save', SubmitType::class, ['label' => 'Save']);
    }

    public function configureOptions(OptionsResolver $resolver): void {
        $resolver->setDefaults([
            'data_class' => Sample::class,
        ]);
    }
}

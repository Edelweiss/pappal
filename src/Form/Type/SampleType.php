<?php
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Sample;

class SampleType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options): void {
      $builder->add('hgv', TextType::class, ['required' => false]); // e.g. 1915a
      $builder->add('ddb', TextType::class, ['required' => false]); // e.g. bgu
      $builder->add('dateWhen', TextType::class, ['required' => false]); 
      $builder->add('dateNotBefore', TextType::class, ['required' => false]);
      $builder->add('dateNotAfter', TextType::class, ['required' => false]);
      $builder->add('title', TextType::class, ['required' => false]);
      $builder->add('material', ChoiceType::class, ['choices' => ['Papyrus' => 'Papyrus', 'Ostrakon' => 'Ostrakon', 'Tafeln' => 'tafel'], 'preferred_choices' => [''], 'required' => false]);
      $builder->add('keywords', TextType::class, ['required' => false]);
      $builder->add('provenance', TextType::class, ['required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void {
        $resolver->setDefaults([
            'data_class' => Sample::class,
        ]);
    }

    /*public function getDefaultOptions(array $options)
    {
        return [
            'data_class' => 'Papyrillio\PapPalBundle\Entity\Sample',
        ];
    }

    public function getName()
    {
        return 'sample';
    }*/
}

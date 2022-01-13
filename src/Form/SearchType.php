<?php

namespace App\Form;

use App\Entity\Country;
use App\Entity\Genre;
use App\Search\Search;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        
            ->add('s', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Search by name'
                ]
            ])

            ->add('countries', EntityType::class, [
                'label' => false,
                'required' => false,
                'class' => Country::class,
                'expanded' => true,
                'multiple' => true
            ])

            ->add('categories', EntityType::class, [
                'label' => false,
                'required' => false,
                'class' => Genre::class,
                'expanded' => true,
                'multiple' => true
            ])

            ->add('followed', CheckboxType::class, [
                'label' => 'Series I follow',
                'required' => false,
            ])

            ->add('submit', SubmitType::class, [
                'label' => 'Search',
                'attr' => [
                    'class' => 'btn btn-danger w-100'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Search::class,
            'method' => 'GET'
        ]);
    }
}

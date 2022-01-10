<?php

namespace App\Form;

use App\Entity\Rating;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class RateType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'comment',
            TextType::class,
            [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Edit a comment here.'
                ]
            ]
        )
        ->add('value' , NumberType::class, [
            'label' => false,
            'attr' => [
                'placeholder' => 'Rate from 1 to 10.'
            ],
            'constraints' => [
                new NotBlank([
                    'message' => 'Please enter a rating value'
                ])
            ]
        ]);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Rating::class,
            'method' => 'GET'
        ]);
    }
}

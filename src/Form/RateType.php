<?php

namespace App\Form;

use App\Entity\Rating;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RateType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

        // Rating number
        ->add('value' , ChoiceType::class, [
            'choices'  => [
                '1' => 1,
                '2' => 2,
                '3' => 3,
                '4' => 4,
                '5' => 5,
                '6' => 6,
                '7' => 7,
                '8' => 8,
                '9' => 9,
                '10' => 10,
            ]
        ])

        // Comment
        ->add('comment', TextType::class, [
                'label' => 'Leave a comment',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Type your comment here.'
                ]
            ]
        )

        // Save button
        ->add('save', SubmitType::class, [
            'label' => 'Save rating',
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

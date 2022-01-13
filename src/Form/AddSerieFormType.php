<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class AddSerieFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

        // Comment
        ->add('imdb_id', TextType::class, [
                'label' => "Serie's IMDb id",
                'attr' => [
                    'placeholder' => 'IMDb id'
                ]
            ]
        )

        // Submit button
        ->add('submit', SubmitType::class, [
            'label' => 'Search',
        ]);
    }
}

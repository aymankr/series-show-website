<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class AddSerieFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

        // Imdb id to  enter
        ->add('imdbId', TextType::class, [
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

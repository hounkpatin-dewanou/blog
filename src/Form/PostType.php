<?php

namespace App\Form;

use App\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('title', null, [
            'attr' => ['placeholder' => 'Titre de l\'article'],
            'required' => true, // Force le "required" HTML
        ])
        ->add('content', null, [
            'attr' => ['placeholder' => 'Contenu de l\'article', 'rows' => 10],
            'required' => true, // Force le "required" HTML
        ])
    ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}

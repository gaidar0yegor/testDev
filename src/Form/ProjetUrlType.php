<?php

namespace App\Form;

use App\Entity\ProjetUrl;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ProjetUrlType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('url', null, [
                'help' => 'projet.projetUrl.url.help',
            ])
            ->add('text', null, [
                'help' => 'projet.projetUrl.text.help',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProjetUrl::class,
        ]);
    }
}

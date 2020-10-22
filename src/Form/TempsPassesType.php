<?php

namespace App\Form;

use App\DTO\ListeTempsPasses;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TempsPassesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('tempsPasses', CollectionType::class, [
                'entry_type' => TempsPasseType::class,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Mettre à jour',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ListeTempsPasses::class,
        ]);
    }
}

<?php

namespace App\Form;

use App\Entity\TempsPasse;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TempsPasseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pourcentage', NumberType::class, [
                'label' => 'Pourcentage',

                // Validation front
                'html5' => true,
                'attr' => [
                    'min' => 0,
                    'max' => 100,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TempsPasse::class,
        ]);
    }
}

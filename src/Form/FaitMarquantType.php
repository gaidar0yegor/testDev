<?php

namespace App\Form;

use App\Entity\FaitMarquant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;


class FaitMarquantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('titre', null, [
                'attr' => ['class' => 'form-control-lg'],
            ])
            ->add('description', TextareaType:: class, [
                'attr' => [
                    'class' => 'text-justify',
                    'rows' => 9
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FaitMarquant::class,
        ]);
    }
}

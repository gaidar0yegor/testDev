<?php

namespace App\Form;

use App\Form\Custom\DatePickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GenerateFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('dateDebut', DatePickerType::class, [
            'required' => false,
            'label' => 'Date de début',
        ])
        ->add('dateFin', DatePickerType::class, [
            'required' => false,
            'label' => 'Date de fin',
        ])
        ->add('submit', SubmitType::class, [
            'label' => 'Générer',
            'attr' => [
                'class' => 'btn btn-success',]
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}

<?php

namespace App\Form;

use App\DTO\ProjetExportParameters;
use App\Form\Custom\DatePickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjetExportType extends AbstractType
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
        ->add('exportOptions', ChoiceType::class, [
            'required' => false,
            'label' => 'Que souhaitez-vous exporter ?',
            'expanded' => true,
            'multiple' => true,
            'choices' => array_combine($options['data']->getExportOptions(),$options['data']->getExportOptions())
        ])
        ->add('format', ChoiceType::class, [
            'choices' => [
                '.pdf' => 'pdf',
                'Page html' => 'html',
            ],
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
            'data_class' => ProjetExportParameters::class
        ]);
    }
}

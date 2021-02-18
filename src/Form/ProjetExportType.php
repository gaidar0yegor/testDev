<?php

namespace App\Form;

use App\DTO\ProjetExportParameters;
use App\Form\Custom\DatePickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
        ->add('format', ChoiceType::class, [
            'choices' => [
                '.pdf' => 'pdf',
                // Disabled .ods because spreadsheet library badly supports this format:
                // exporting in .xlsx then open it with Libreoffice leads to better results
                //'.ods (LibreOffice)' => 'ods',
                // '.xlsx (Excel, LibreOffice)' => 'xlsx',
                // '.xls (Excel 2003)' => 'xls',
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

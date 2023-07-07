<?php

namespace App\Form;

use App\DTO\FilterTimesheet;
use App\Form\Custom\MonthType;
use App\Form\Custom\SameSocieteUserType;
use App\Form\Custom\SameTeamUserType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class FilterTimesheetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('from', MonthType::class, [
                'label' => 'À partir de',
                'help' => 'inclus.',
            ])
            ->add('to', MonthType::class, [
                'label' => 'Jusqu\'au',
                'help' => 'inclus.',
            ])
            ->add('format', ChoiceType::class, [
                'choices' => [
                    '.pdf' => 'pdf',
                    // Disabled .ods because spreadsheet library badly supports this format:
                    // exporting in .xlsx then open it with Libreoffice leads to better results
                    //'.ods (LibreOffice)' => 'ods',
                    '.xlsx (Excel, LibreOffice)' => 'xlsx',
                    '.xls (Excel 2003)' => 'xls',
                    'Page html' => 'html',
                ],
            ])
            ->add('users', $options['forTeamMembers'] ? SameTeamUserType::class : SameSocieteUserType::class, [
                'required' => false,
                'expanded' => true,
                'multiple' => true,
                'label' => 'Feuilles de temps des utilisateurs :',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Générer',
                'attr' => [
                    'class' => 'btn btn-success',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FilterTimesheet::class,
            'forTeamMembers' => false
        ]);
    }
}

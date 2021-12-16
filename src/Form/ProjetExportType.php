<?php

namespace App\Form;

use App\DTO\ProjetExportParameters;
use App\Form\Custom\DatePickerType;
use App\Security\Role\RoleSociete;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ProjetExportType extends AbstractType
{
    private AuthorizationCheckerInterface $authChecker;

    public function __construct(AuthorizationCheckerInterface $authChecker)
    {
        $this->authChecker = $authChecker;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $exportOptions = array_combine($options['data']->getExportOptions(),$options['data']->getExportOptions());

        if (!$this->authChecker->isGranted(RoleSociete::ADMIN)) {
            unset($exportOptions[ProjetExportParameters::STATISTIQUES]);
            unset($exportOptions[ProjetExportParameters::PARTICIPANTS]);
        }

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
            'choices' => $exportOptions
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

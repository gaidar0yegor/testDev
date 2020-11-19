<?php

namespace App\Form;

use App\DTO\FilterTimesheet;
use App\Form\Custom\SameSocieteUserType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class FilterTimesheetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('users', SameSocieteUserType::class, [
                'required' => false,
                'expanded' => true,
                'multiple' => true,
                'label' => 'Feuilles de temps des utilisateurs :',
            ])
            ->add('from', DateType::class, [
                'days' => [1],
                'label' => 'À partir de',
                'help' => 'inclus.'
            ])
            ->add('to', DateType::class, [
                'days' => [1],
                'label' => 'Jusqu\'au',
                'help' => 'inclus.'
            ])
            ->add('format', ChoiceType::class, [
                'choices' => [
                    'pdf' => 'pdf',
                    'html' => 'html',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Générer',
                'attr' => [
                    'class' => 'mt-5 btn btn-primary',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FilterTimesheet::class,
        ]);
    }
}

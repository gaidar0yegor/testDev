<?php

namespace App\Form;

use App\Entity\Societe;
use App\Form\Custom\CardChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SocieteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('raisonSociale')
            ->add('siret')
            ->add('heuresParJours', NumberType::class)
            ->add('timesheetGranularity', CardChoiceType::class, [
                'label' => 'Saisie des temps',
                'help' => join(' ', [
                    'Au mois :',
                    'les utilisateurs doivent saisir leurs temps passés une fois par mois seulement.',
                    'À la semaine :',
                    'les pourcentages sont plus précis, mais les utilisateurs doivent les saisir chaque semaine.',
                ]),
                'choices' => [
                    Societe::GRANULARITY_MONTHLY => Societe::GRANULARITY_MONTHLY,
                    Societe::GRANULARITY_WEEKLY => Societe::GRANULARITY_WEEKLY,
                    Societe::GRANULARITY_DAILY => Societe::GRANULARITY_DAILY,
                ],
                'faIcons' => [
                    Societe::GRANULARITY_MONTHLY => 'fa-calendar-o',
                    Societe::GRANULARITY_WEEKLY => 'fa-calendar-minus-o',
                    Societe::GRANULARITY_DAILY => 'fa-calendar',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Societe::class,
        ]);
    }
}

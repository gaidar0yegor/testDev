<?php

namespace App\Form;

use App\Entity\Societe;
use App\Form\Custom\CardChoiceType;
use App\SocieteProduct\Product\ProductPrivileges;
use App\SocieteProduct\ProductPrivilegeCheker;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SocieteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $societe = $builder->getData();

        $builder
            ->add('raisonSociale')
            ->add('siret')
            ->add('heuresParJours', NumberType::class)
            ->add('coutEtp', NumberType::class, [
                'label' => "Coût moyen horaire de l'ETP (€)"
            ])
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
            ]);
        if (ProductPrivilegeCheker::checkProductPrivilege($societe, ProductPrivileges::FAIT_MARQUANT_DESCRIPTION_SIZE)) {
            $builder
                ->add('faitMarquantMaxDesc', ChoiceType::class, [
                    'label' => 'max_legnth_fait_marquant_desc',
                    'choices' => [
                        '750 caractères' => 751,
                        '1000 caractères' => 1001,
                        '1500 caractères' => 1501,
                        'Illimité' => -1,
                    ],
                ])
                ->add('faitMarquantMaxDescIsblocking', CheckboxType::class, [
                    'label' => 'isBlocking',
                    'required' => false,
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Societe::class,
        ]);
    }
}

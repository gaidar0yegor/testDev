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
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\DTO\ListCurrencies;

class SocieteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $societe = $builder->getData();

        $builder
            ->add('raisonSociale')
            ->add('siret')
            ->add('heuresParJours', NumberType::class, [
                'label' => "Heures par jour"
            ])
            ->add('workStartTime', TextType::class, [
                'label' => "Heure de début du travail",
                'attr' => [
                    'placeholder' => Societe::DEFAULT_WORK_START_TIME
                ]
            ])
            ->add('workEndTime', TextType::class, [
                'label' => "Heure de fin du travail",
                'attr' => [
                    'placeholder' => Societe::DEFAULT_WORK_END_TIME
                ]
            ])
            ->add('coutEtp', NumberType::class, [
                'label' => "Coût moyen horaire de l'ETP ({$societe->getCurrency()}/h)"
            ])
            ->add('currency', ChoiceType::class, [
                'label' => "Devise",
                'choices' => array_flip(ListCurrencies::getCurrencies()),
                'preferred_choices' => array_flip(ListCurrencies::getPreferredCurrencies()),
                'attr' => [
                    'class' => 'select-2 form-control'
                ],
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
            ])
            ->addEventListener(FormEvents::SUBMIT, [$this, 'verifyHeuresParJours'])
        ;
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

    public function verifyHeuresParJours(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();
        $workStartTime = new \DateTime($data->getWorkStartTime() . ':00');
        $workEndTime = new \DateTime($data->getWorkEndTime() . ':00');
        $diff = $workStartTime->diff($workEndTime)->h;

        if ($data->getHeuresParJours() > $diff){
            $form->addError(new FormError("Le nombre d'heures de travail n'est pas compatible avec la plage horaire indiquée."));
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Societe::class,
        ]);
    }
}

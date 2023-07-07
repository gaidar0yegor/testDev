<?php

namespace App\Form;

use App\Entity\ProjetParticipant;
use App\Entity\SocieteUserPeriod;
use App\Form\Custom\DatePickerType;
use App\Form\Custom\RoleProjetCardChoiceType;
use App\Form\Custom\SameSocieteUserType;
use App\ProjetResourceInterface;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\Form\Event\SubmitEvent;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SocieteUserPeriodType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dateEntry', DatePickerType::class, [
                'label' => 'societeUserPeriod.dateEntry',
                'required' => true,
            ])
            ->add('dateLeave', DatePickerType::class, [
                'label' => 'societeUserPeriod.dateLeave',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SocieteUserPeriod::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'societe_user_periods';
    }
}

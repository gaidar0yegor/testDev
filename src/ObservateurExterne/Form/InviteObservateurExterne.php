<?php

namespace App\ObservateurExterne\Form;

use App\Entity\ProjetObservateurExterne;
use App\Form\Custom\RdiMobilePhoneNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InviteObservateurExterne extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('invitationEmail', EmailType::class, [
                'required' => false,
                'label' => 'Email',
            ])
            ->add('invitationTelephone', RdiMobilePhoneNumberType:: class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProjetObservateurExterne::class,
            'validation_groups' => ['Default', 'invitation'],
        ]);
    }
}

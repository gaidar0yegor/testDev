<?php

namespace App\Form;

use App\Entity\SocieteUser;
use App\Form\Custom\RdiPhoneNumberType;
use App\Security\Role\RoleSociete;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InviteUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('invitationEmail', EmailType::class, [
                'required' => false,
                'label' => 'Email',
            ])
            ->add('invitationTelephone', RdiPhoneNumberType::class, [
                'required' => false,
                'label' => 'Téléphone',
            ])
            ->add('role',  ChoiceType::class, [
                'choices' => [
                    RoleSociete::USER => RoleSociete::USER,
                    RoleSociete::CDP => RoleSociete::CDP,
                    RoleSociete::ADMIN => RoleSociete::ADMIN,
                ],
            ])
            ->add('ajouter', SubmitType::class, [
                'label' => 'Inviter',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SocieteUser::class,
            'validation_groups' => ['Default', 'invitation'],
        ]);
    }
}

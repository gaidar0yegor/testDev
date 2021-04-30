<?php

namespace App\Form;

use App\DTO\InvitationUserSurProjet;
use App\Form\Custom\RoleProjetCardChoiceType;
use App\Security\Role\RoleProjet;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InviteUserSurProjetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email',
            ])
            ->add('role', RoleProjetCardChoiceType::class, [
                'choices' => [
                    RoleProjet::CONTRIBUTEUR => RoleProjet::CONTRIBUTEUR,
                    RoleProjet::OBSERVATEUR => RoleProjet::OBSERVATEUR,
                ],
                'choice_label' => null,
            ])
            ->add('ajouter', SubmitType::class, [
                'label' => 'Inviter',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => InvitationUserSurProjet::class,
        ]);
    }
}

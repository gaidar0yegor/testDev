<?php

namespace App\Form;

use App\DTO\InvitationUserSurProjet;
use App\Role;
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
            ->add('role', ParticipantRoleChoiceType::class, [
                'choices' => array_filter(array_reverse(Role::getRoles()), function (string $role) {
                    return $role !== Role::CDP;
                }),
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

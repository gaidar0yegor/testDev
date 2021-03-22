<?php

namespace App\Form\Custom;

use App\Role;
use App\Security\Role\RoleProjet;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParticipantRoleChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => array_reverse(RoleProjet::getRoles()),
            'expanded' => true,
            'choice_label' => false,
            'attr' => ['class' => 'form-inline-radio'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'participant_role_choice';
    }
}

<?php

namespace App\Form\Custom;

use App\Security\Role\RoleProjet;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Displays role projet radio choices as fancy bootstrap cards.
 */
class RoleProjetCardChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => [
                RoleProjet::CDP => RoleProjet::CDP,
                RoleProjet::CONTRIBUTEUR => RoleProjet::CONTRIBUTEUR,
                RoleProjet::OBSERVATEUR => RoleProjet::OBSERVATEUR,
            ],
            'faIcons' => [
                RoleProjet::CDP => 'fa-user-circle-o',
                RoleProjet::CONTRIBUTEUR => 'fa-user',
                RoleProjet::OBSERVATEUR => 'fa-eye',
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CardChoiceType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'role_projet_card_choice';
    }
}

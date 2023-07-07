<?php

namespace App\Form\Custom;

use App\Security\Role\RoleSociete;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Displays radio choices as fancy bootstrap cards.
 */
class FoRoleCardChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => [
                RoleSociete::USER => RoleSociete::USER,
                RoleSociete::CDP => RoleSociete::CDP,
                RoleSociete::ADMIN => RoleSociete::ADMIN,
            ],
            'faIcons' => [
                RoleSociete::USER => 'fa-user',
                RoleSociete::CDP => 'fa-user-plus',
                RoleSociete::ADMIN => 'fa-user-circle-o',
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
        return 'fo_role_card_choice';
    }
}

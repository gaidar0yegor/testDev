<?php

namespace App\Form\Custom;

use App\Security\Role\RoleLabo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Displays radio choices as fancy bootstrap cards.
 */
class LabAppFoRoleCardChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => [
                RoleLabo::USER => RoleLabo::USER,
                RoleLabo::SENIOR => RoleLabo::SENIOR,
                RoleLabo::ADMIN => RoleLabo::ADMIN,
            ],
            'faIcons' => [
                RoleLabo::USER => 'fa-user',
                RoleLabo::SENIOR => 'fa-user-plus',
                RoleLabo::ADMIN => 'fa-user-circle-o',
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

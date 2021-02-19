<?php

namespace App\Form\Custom;

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
                'ROLE_FO_USER' => 'ROLE_FO_USER',
                'ROLE_FO_CDP' => 'ROLE_FO_CDP',
                'ROLE_FO_ADMIN' => 'ROLE_FO_ADMIN',
            ],
            'faIcons' => [
                'ROLE_FO_USER' => 'fa-user',
                'ROLE_FO_CDP' => 'fa-user-plus',
                'ROLE_FO_ADMIN' => 'fa-user-circle-o',
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

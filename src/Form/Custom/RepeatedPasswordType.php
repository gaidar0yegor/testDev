<?php

namespace App\Form\Custom;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RepeatedPasswordType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'type' => PasswordType::class,
            'invalid_message' => 'Erreur dans l\'un des deux mots de passe.',
            'first_options'  => ['label' => 'Définissez votre mot de passe'],
            'second_options' => ['label' => 'Répétez votre mot de passe'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return RepeatedType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'repeated_password';
    }
}

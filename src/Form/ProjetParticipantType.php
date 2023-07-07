<?php

namespace App\Form;

use App\Entity\ProjetParticipant;
use App\Form\Custom\RoleProjetCardChoiceType;
use App\Form\Custom\SameSocieteUserType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjetParticipantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('societeUser', SameSocieteUserType::class, [
                'placeholder' => 'Liste des utilisateurs',
                'attr' => [
                    'class' => 'select-2 form-control',
                    'data-placeholder' => 'Liste des utilisateurs',
                ],
            ])
            ->add('role', RoleProjetCardChoiceType::class, [
                'card_choice_size' => 'small',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProjetParticipant::class,
        ]);
    }
}

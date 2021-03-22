<?php

namespace App\Form;

use App\Entity\ProjetParticipant;
use App\Form\Custom\ParticipantRoleChoiceType;
use App\Form\Custom\SameSocieteUserType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjetParticipantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('societeUser', SameSocieteUserType::class)
            ->add('role', ParticipantRoleChoiceType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProjetParticipant::class,
        ]);
    }
}

<?php

namespace App\Form;

use App\Entity\SocieteUser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Formulaire pour modifier les rôles d'un user sur tous ses projets.
 */
class SocieteUserProjetsRolesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('projetParticipants', CollectionType::class, [
                'entry_type' => ProjetParticipantProjetRoleType::class,
                'label' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SocieteUser::class,
        ]);
    }
}

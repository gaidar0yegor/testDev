<?php

namespace App\RegisterSociete\Form;

use App\Form\Custom\FoRoleCardChoiceType;
use App\RegisterSociete\DTO\InviteCollaborators;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CollaboratorsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email0', EmailType::class, [
                'required' => false,
                'label' => 'Email',
            ])
            ->add('role0', FoRoleCardChoiceType::class, [
                'label' => 'Rôle',
            ])
            ->add('email1', EmailType::class, [
                'required' => false,
                'label' => 'Email',
            ])
            ->add('role1', FoRoleCardChoiceType::class, [
                'label' => 'Rôle',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => InviteCollaborators::class,
        ]);
    }
}

<?php

namespace App\Form;

use App\Entity\ProjetParticipant;
use App\Entity\Societe;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjetParticipantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
                'query_builder' => function (UserRepository $er) use ($options) {
                    if (!isset($options['societe'])) {
                        return null;
                    }

                    return $er->whereSociete($options['societe']);
                },
            ])
            ->add('role', ParticipantRoleChoiceType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProjetParticipant::class,
            'societe' => null,
        ]);

        $resolver->setAllowedTypes('societe', ['null', Societe::class]);
    }
}

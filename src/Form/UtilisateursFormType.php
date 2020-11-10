<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UtilisateursFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('nom', TextType::class, [
            'label' => 'Nom',
        ])
        ->add('prenom', TextType::class, [
            'label' => 'Prénom',
        ])
        ->add('telephone', TextType::class, [
            'label' => 'Mobile',
            'required' => false,
        ])
        ->add('email', EmailType::class, [
            'label' => 'Email',
        ])
        ->add('role',  ChoiceType::class, [
            'choices' => [
                'Utilisateur' => 'ROLE_FO_USER',
                'Chef de projet' => 'ROLE_FO_CDP',
                'Référent' => 'ROLE_FO_ADMIN',
            ],
        ])
        ->add('ajouter', SubmitType::class, [
            'label' => 'Mettre à jour',
            'attr' => [
                'class' => 'mt-5 btn-rdi',
                ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}

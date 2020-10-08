<?php

namespace App\Form;

use App\Entity\Users;
// use App\Entity\Societes;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
// use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UtilisateursFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('nom', TextType::class, [
            "label" => "Nom",
            "attr" => [
                "class" => "form-control"
            ]
        ])
        ->add('prenom', TextType::class, [
            "label" => "Prénom",
            "attr" => [
                "class" => "form-control"
            ]
        ])
        ->add('telephone', Numbertype::class, [
            "label" => "Mobile",
            "attr" => [
                "class" => "form-control"
            ]
        ])
        ->add('email', EmailType::class, [
            "label" => "Email",
            "attr" => [
                "class" => "form-control"
            ]
        ])
        // ->add('societes')
        ->add('roles',  ChoiceType::class, [
            "label" => "Rôle",
            "attr" => [
                "class" => "form-control"
            ],
            "multiple" => true,
            "choices" => [
                "Membre" => "ROLE_USER",
                "Contributeur" => "ROLE_CONTRIBUTEUR",
                "Administrateur" => "ROLE_ADMIN",   
                "Développeur" => "ROLE_DEV"
            ],
        ])
        ->add('ajouter', SubmitType::class, [
            "label" => "Ajouter",
            "attr"=> [
                "class" => "mt-5 btn btn-success"
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Users::class,
        ]);
    }
}


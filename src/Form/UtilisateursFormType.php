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
            'label' => 'Rôle',
            'choices' => [
                'Utilisateur' => 'ROLE_FO_USER',
                'C. Projet' => 'ROLE_FO_CDP',
                'Administrateur' => 'ROLE_FO_ADMIN',
            ],
        ])
        ->add('heuresParJours', NumberType::class, [
            'label' => 'Nombre d\'heures travaillées par jour',
            'help' => 'Pour cet utilisateur uniquement, vous pouvez remplacer ici le nombre d\'heure défini globalement au niveau de la société.',
            'required' => false,
            'attr' => [
                'placeholder' => $this->getHeuresPlaceholder($builder),
            ],
        ])
        ->add('ajouter', SubmitType::class, [
            'label' => 'Mettre à jour',
            'attr' => [
                'class' => 'mt-5 btn btn-success',
                ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }

    private function getHeuresPlaceholder(FormBuilderInterface $builder): string
    {
        $defaultHeuresParJour = $builder->getData()->getSociete()->getHeuresParJours();

        if (null === $defaultHeuresParJour) {
            return '';
        }

        return sprintf('Par défaut : %.2f', $defaultHeuresParJour);
    }
}

<?php

namespace App\Form;

use App\Entity\Projet;
use App\Entity\StatutProjet;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateType;


class ProjetFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('acronyme', TextType::class, [
                'label' => 'Acronyme',
            ])
            ->add('titre', TextType::class, [
                'label' => 'Titre',
            ])
            ->add('resume', TextareaType::class, [
                'label' => 'Résumé',
                'required' => false,
            ])
            ->add('dateDebut', DateType::class, [
                'label' => 'Date de début',
            ])
            ->add('dateFin', DateType::class, [
                'label' => 'Date de fin',
            ])
            ->add('projetInterne', CheckboxType::class, [
                'label' => 'Projet en collaboration avec au moins un partenaire externe',
                'required' => false,
            ])
            ->add('projetCollaboratif', CheckboxType::class, [
                'label' => 'Projet en collaboration avec un partenaire universitaire',
                'required' => false,
            ])
            ->add('projetPpp', CheckboxType::class, [
                'label' => 'Projet réalisé en interne par la société (avec ou sans prestataires)',
                'required' => false,
            ])
            ->add('statutProjet', EntityType::class, [
                'class' => StatutProjet::class,
                'choice_label' => 'libelle',
            ])
            ->add('ajouter', SubmitType::class, [
                "label" => "Ajouter",
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Projet::class,
        ]);
    }
}

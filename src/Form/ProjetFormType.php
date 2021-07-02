<?php

namespace App\Form;

use App\Entity\Projet;
use App\Form\Custom\DatePickerType;
use App\Form\Custom\MarkdownWysiwygType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class ProjetFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre descriptif',
            ])
            ->add('acronyme', TextType::class, [
                'label' => 'Titre réduit / Acronyme',
                'attr' => ['class' => 'form-control-lg'],
            ])
            ->add('resume', MarkdownWysiwygType::class, [
                'label' => 'Résumé',
                'required' => false,
                'attr' => [
                    'class' => 'text-justify',
                    'rows' => 15,
                ],
            ])
            ->add('dateDebut', DatePickerType::class, [
                'required' => false,
                'label' => 'Date de début',
            ])
            ->add('dateFin', DatePickerType::class, [
                'required' => false,
                'label' => 'Date de fin',
            ])
            ->add('projetUrls', CollectionType::class, [
                'label' => 'projet.projetUrls',
                'help' => 'projet.projetUrls.help',
                'entry_type' => ProjetUrlType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
            ->add('projetCollaboratif', CheckboxType::class, [
                'label' => 'Projet en collaboration avec au moins un partenaire externe',
                'required' => false,
            ])
            ->add('projetPpp', CheckboxType::class, [
                'label' => 'Projet en collaboration avec un partenaire universitaire',
                'required' => false,
            ])
            ->add('projetInterne', CheckboxType::class, [
                'label' => 'Projet réalisé en interne par la société (avec ou sans prestataires)',
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Soumettre',
                'attr' => [
                    'class' => 'mt-5 btn btn-success',
                ],
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

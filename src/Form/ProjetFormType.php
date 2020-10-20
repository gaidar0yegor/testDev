<?php

namespace App\Form;

use App\Entity\Projets;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateType;


class ProjetFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('acronyme', NumberType::class, [
                "label" => "Projet",
                "attr" => [
                    "class" => "form-control"
                ]
            ])        
            ->add('titre', TextType::class, [
                "label" => "Titre",
                "attr" => [
                    "class" => "form-control"
                ]
            ])
            ->add('resume', TextareaType::class, [
                "label" => "Résumé",
                "attr" => [
                    "rows" => 9,
                    "class" => "form-control"
                ]
            ])
            ->add('chef_de_projet', TextType::class, [
                "label" => "Chef de projet",
                "attr" => [
                    "class" => "form-control"
                ]
            ])
            ->add('date_debut', DateType::class, [  
                'label' => 'Date de début',
                "attr" => [
                    "class" => "form-control"
                ]
            ])
            ->add('date_fin', DateType::class, [
                'label' => 'Date de fin',
                "attr" => [
                    "class" => "form-control"
                ]
            ])
            //->add('statut_rdi')
            ->add('projet_interne', CheckboxType::class, [
                'label' => 'Projet en collaboration avec au moins un partenaire externe',
            ])
            ->add('projet_collaboratif', CheckboxType::class, [
                'label' => 'Projet en collaboration avec un partenaire universitaire',               
            ])
            ->add('projet_ppp', CheckboxType::class, [
                'label' => 'Projet réalisé en interne par la société (avec ou sans prestataires)',               
            ])
            //->add('statuts_projet')
            ->add('ajouter', SubmitType::class, [
                "label" => "Ajouter",
                "attr"=> [
                    "class" => "mt-5 btn btn-success"
                ]])
                ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Projets::class,
        ]);
    }
}

// ->add('acronyme', [
//     "label" => "Acronyme",
//     "attr" => [
//         "class" => "form-control"
//     ]
// ])    

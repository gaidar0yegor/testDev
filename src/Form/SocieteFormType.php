<?php

namespace App\Form;

use App\Entity\Societe;
use App\Entity\User;
use App\Entity\SocieteStatut;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;




class SocieteFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('raisonSociale', TextType::class, [
                'label' => 'Nom de la société',
            ])
            ->add('siret', Numbertype::class, [
                'label' => 'SIRET',
                'required' => false,
            ])
            // ->add('chemin_logo')
            // ->add('nom_logo')
            ->add('statut', EntityType::class, [
                'class' => SocieteStatut::class,
                'choice_label' => 'libelle',
            ])
            ->add('nb_licences', Numbertype::class, [
                'label' => 'Nb de licences',
                'required' => false,
            ])
            ->add('nb_licences_dispo', Numbertype::class, [
                'label' => 'Nb de licences disponibles',
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Ajouter',
                'attr' => [
                    'class' => 'mt-5 btn btn-success'
                ]    
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Societe::class,
        ]);
    }
}

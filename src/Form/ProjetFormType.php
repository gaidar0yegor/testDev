<?php

namespace App\Form;

use App\Entity\Projets;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ProjetFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('acronyme')    
            ->add('titre')
            ->add('resume')
            ->add('date_debut')
            ->add('date_fin')
            //->add('statut_rdi')
            ->add('projet_interne')
            ->add('projet_collaboratif')
            ->add('projet_ppp')
            ->add('statuts_projet')
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

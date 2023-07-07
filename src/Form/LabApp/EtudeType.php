<?php

namespace App\Form\LabApp;

use App\Entity\LabApp\Etude;
use App\Form\Custom\DatePickerType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class EtudeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'etude.title',
                'required' => true,
            ])
            ->add('acronyme', TextType::class, [
                'label' => 'etude.acronyme',
                'required' => true,
            ])
            ->add('resume', CKEditorType::class, [
                'label' => 'etude.resume',
                'required' => true,
                'attr' => [
                    'class' => 'ckeditor-instance',
                ],
            ])
            ->add('dateDebut', DatePickerType::class, [
                'label' => 'etude.dateDebut',
                'required' => false,
            ])
            ->add('dateFin', DatePickerType::class, [
                'label' => 'etude.dateFin',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Etude::class,
        ]);
    }
}

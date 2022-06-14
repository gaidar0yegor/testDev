<?php

namespace App\RegisterLabo\Form;

use App\Entity\LabApp\Labo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LaboType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, [
                'attr' => [
                    'class' => 'form-control-lg',
                ],
                'help' => 'Nom de votre laboratoire tel qu\'il apparaîtra dans votre interface.',
            ])
            ->add('rnsr', null, [
                'label' => 'RNSR <i class="fa fa-question-circle" title="Répertoire national des structures de recherche"></i>',
                'label_html' => true,
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Labo::class,
            'validation_groups' => ['registration'],
        ]);
    }
}

<?php

namespace App\RegisterLabo\Form;

use App\Entity\LabApp\Labo;
use App\Repository\LabApp\LaboRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LaboType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('existedLabo', EntityType::class, [
                'label' => 'Intégrer un laboratoire',
                'class' => Labo::class,
                'query_builder' => function (LaboRepository $repository) {
                    return $repository->createQueryBuilder('labo');
                },
                'choice_label' => function (Labo $labo) {
                    return $labo->getRnsr() . ' - ' . $labo->getName();
                },
                'mapped' => false,
                'required' => false,
                'placeholder' => 'Chercher votre laboratoire ...',
                'attr' => [
                    'class' => 'select-2 form-control',
                ],
            ])
            ->add('name', null, [
                'attr' => [
                    'class' => 'form-control-lg',
                ],
                'required' => false,
                'help' => 'Nom de votre laboratoire tel qu\'il apparaîtra dans votre interface.',
            ])
            ->add('rnsr', null, [
                'label' => 'RNSR <i class="fa fa-question-circle" title="Répertoire national des structures de recherche"></i>',
                'label_html' => true,
                'required' => false,
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

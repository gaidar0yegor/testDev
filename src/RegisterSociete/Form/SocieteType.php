<?php

namespace App\RegisterSociete\Form;

use App\Entity\Societe;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SocieteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('raisonSociale', null, [
                'attr' => [
                    'class' => 'form-control-lg',
                ],
                'help' => 'Nom de votre société tel qu\'il apparaîtra dans votre interface.',
            ])
            ->add('siret', null, [
                'required' => false,
                'help' => 'Optionelle, vous pourrez le remplir plus tard.',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Societe::class,
            'validation_groups' => ['registration'],
        ]);
    }
}

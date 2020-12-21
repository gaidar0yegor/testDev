<?php

namespace App\Form;

use App\DTO\InitSociete;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InitSocieteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('raisonSociale', null, [
                'required' => true,
            ])
            ->add('siret', null, [
                'help' => 'Optionnel, le nouvel administrateur pourra le saisir.',
            ])
            ->add('adminEmail', EmailType::class, [
                'label' => 'Email du nouvel administrateur',
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => InitSociete::class,
        ]);
    }
}

<?php

namespace App\RegisterSociete\Form;

use App\Entity\User;
use App\Form\Custom\RepeatedPasswordType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Votre adresse email',
                'help' => 'Cet email sera utilisé pour vous connecter.',
            ])
            ->add('prenom', null, [
                'label' => 'Prénom',
            ])
            ->add('nom')
            ->add('password', RepeatedPasswordType::class)
            ->add('acceptCguCgv', CheckboxType::class, [
                'label' => 'i_accept_cgu_and_cgv',
                'label_html' => true,
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'validation_groups' => ['Default', 'registration'],
        ]);
    }
}

<?php

namespace App\Form;

use App\Entity\User;
use App\Form\Custom\RdiMobilePhoneNumberType;
use App\Form\Custom\RepeatedPasswordType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FinalizeInscriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('prenom', TextType::class, [
                'label' => 'firstname',
            ])
            ->add('nom', TextType::class, [
                'label' => 'lastname',
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
            ])
            ->add('telephone', RdiMobilePhoneNumberType::class)
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
        ]);
    }
}

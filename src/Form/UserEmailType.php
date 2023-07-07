<?php

namespace App\Form;

use App\Entity\SocieteUser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserEmailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('invitationEmail', EmailType::class, [
                'label' => 'Email',
            ])
            ->add('societeUserPeriods', CollectionType::class, [
                'label' => false,
                'entry_type' => SocieteUserPeriodType::class,
                'required' => false,
                'allow_add' => false,
                'allow_delete' => false,
                'by_reference' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SocieteUser::class,
        ]);
    }
}

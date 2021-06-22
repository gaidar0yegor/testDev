<?php

namespace App\Form;

use App\Entity\SocieteUser;
use App\Form\Custom\FoRoleCardChoiceType;
use App\Form\Custom\RdiMobilePhoneNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InviteUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('invitationEmail', EmailType::class, [
                'required' => false,
                'label' => 'email',
            ])
            ->add('invitationTelephone', RdiMobilePhoneNumberType::class, [
                'required' => false,
                'label' => 'phone',
            ])
            ->add('role', FoRoleCardChoiceType::class, [
                'label' => 'role',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SocieteUser::class,
            'validation_groups' => ['Default', 'invitation'],
        ]);
    }
}

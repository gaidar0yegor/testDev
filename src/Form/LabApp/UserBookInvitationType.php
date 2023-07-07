<?php

namespace App\Form\LabApp;

use App\Entity\LabApp\UserBookInvite;
use App\Form\Custom\LabAppFoRoleCardChoiceType;
use App\Form\Custom\RdiMobilePhoneNumberType;
use App\Form\EventListener\CheckPeriodsDatesListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserBookInvitationType extends AbstractType
{
    private CheckPeriodsDatesListener $checkPeriodsDatesListener;

    public function __construct(CheckPeriodsDatesListener $checkPeriodsDatesListener)
    {
        $this->checkPeriodsDatesListener = $checkPeriodsDatesListener;
    }

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
            ->add('role', LabAppFoRoleCardChoiceType::class, [
                'label' => 'role',
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'Notes',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserBookInvite::class,
            'validation_groups' => ['Default', 'invitation'],
        ]);
    }
}

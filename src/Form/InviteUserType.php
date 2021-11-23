<?php

namespace App\Form;

use App\Entity\SocieteUser;
use App\Form\Custom\FoRoleCardChoiceType;
use App\Form\Custom\RdiMobilePhoneNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\SubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvents;
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
            ->add('societeUserPeriods', CollectionType::class, [
                'label' => false,
                'entry_type' => SocieteUserPeriodType::class,
                'allow_add' => true,
                'allow_delete' => true,
//            'by_reference' => false,
            ])
            ->addEventListener(FormEvents::SUBMIT, [$this, 'checkDates'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SocieteUser::class,
            'validation_groups' => ['Default', 'invitation'],
        ]);
    }

    public function checkDates(SubmitEvent $event): void
    {
        $forms = $event->getForm()->get('societeUserPeriods');

        $dateLeaveLast = null;
        foreach ($forms as $societeUserPeriodForm) {
            $dateEntry = $societeUserPeriodForm->getData()->getDateEntry();
            $dateLeave = $societeUserPeriodForm->getData()->getDateLeave();
            if (
                $dateEntry && $dateLeave && $societeUserPeriodForm->getData()->getDateEntry() > $societeUserPeriodForm->getData()->getDateLeave() ||
                $dateEntry && $dateLeaveLast && $dateEntry < $dateLeaveLast
            ) {
                $societeUserPeriodForm->addError(new FormError("Les dates d'entrée / sortie ne sont pas cohérentes !"));
            }
            $dateLeaveLast = $dateLeave;
        }
    }
}

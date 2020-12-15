<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserNotificationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('notificationEnabled', null, [
                'label' => 'Notifications',
                'help' => 'Active ou désactive toutes les notifications. Seuls les mails importants seront recus (mot de passe oublié...).',
            ])
            ->add('notificationSaisieTempsEnabled', null, [
                'label' => 'Rappel de saisie de mes temps et absences',
                'help' => 'Notifications mensuelle pour me rappeler de mettre à jour mes temps passés sur mes projets.',
            ])
            ->add('notificationCreateFaitMarquantEnabled', null, [
                'label' => 'Rappel de création des faits marquants',
                'help' => 'Notifications pour me rappeler de créer des éventuels faits marquants sur mes projets.',
            ])
            ->add('notificationLatestFaitMarquantEnabled', null, [
                'label' => 'Notification des derniers faits marquants ajoutés',
                'help' => 'Notifications type newsletter pour me remonter les derniers faits marquants ajoutés sur mes projets par les autres contributeurs.',
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

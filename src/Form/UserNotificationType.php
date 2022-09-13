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
            ->add('notificationPlanningTaskNotCompletedEnabled', null, [
                'label' => 'Notification de la planification du projet : Echéance de tâche',
                'help' => 'Notification pour vous rappelez mes échéances sur les tâches des projets auxquels je participe.',
            ])
            ->add('notificationPlanningTaskStartSoonEnabled', null, [
                'label' => 'Notification de la planification du projet : Début de tâche',
                'help' => 'Notification pour vous rappelez du début des tâches des projets auxquels je participe.',
            ])
            ->add('notificationEvenementInvitationEnabled', null, [
                'label' => 'Notification de l\'agenda',
                'help' => 'Notification qui permet d\'être informé en cas de nouvel événement, modification d\'un événement ou bien une suppression d\'événement sur un projet.',
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

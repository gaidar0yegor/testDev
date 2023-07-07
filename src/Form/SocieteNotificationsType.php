<?php

namespace App\Form;

use App\DTO\SocieteNotifications;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SocieteNotificationsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('creerFaitsMarquants', CronJobType::class, [
                'dayOfWeek' => true,
                'help' => 'L\'utilisateur reçoit une notification avec un lien vers le formulaire de création de faits marquants sur ses projets.',
            ])
            ->add('derniersFaitsMarquants', CronJobType::class, [
                'dayOfWeek' => true,
                'help' => 'L\'utilisateur reçoit une notification type newsletter avec tous les derniers faits marquants ajoutés sur les projets auxquels il est au moins observateur.',
            ])
            ->add('saisieTemps', CronJobType::class, [
                'dayOfMonth' => true,
                'help' => 'L\'utilisateur reçoit une notification avec un lien vers le formulaire de saisie de ses temps passés sur les projets auxquels il contribue.',
            ])
            ->add('smsEnabled', CheckboxType::class, [
                'required' => false,
                'label' => 'Utiliser les notifications SMS',
                'help' => 'Un SMS sera également envoyé aux utilisateurs ayant saisi leurs numéro de téléphone portable, pour certaines notifications importantes (comme le rappel de saisi des temps).',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'update',
                'attr' => ['class' => 'btn btn-success btn-lg mt-3 d-block mx-auto'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SocieteNotifications::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'societe_notifications';
    }
}

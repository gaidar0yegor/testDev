<?php

namespace App\Form;

use App\DTO\SocieteNotifications;
use Symfony\Component\Form\AbstractType;
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
                'help' => 'L\'utilisateur recoit une notification avec un lien vers le formulaire de création de faits marquants sur ses projets.',
            ])
            ->add('derniersFaitsMarquants', CronJobType::class, [
                'dayOfWeek' => true,
                'help' => 'L\'utilisateur recoit une notification type newsletter avec tous les derniers faits marquants ajoutés sur les projets dont il est au moins observateur.',
            ])
            ->add('saisieTemps', CronJobType::class, [
                'dayOfMonth' => true,
                'help' => 'L\'utilisateur recoit une notification avec un lien vers le formulaire de saisie de ses temps passés sur les projets dont il contribue.',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer',
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

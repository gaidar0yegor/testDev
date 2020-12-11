<?php

namespace App\Form;

use App\Form\Custom\CronScheduleType;
use Cron\CronBundle\Entity\CronJob;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CronJobType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('enabled', null, [
                'label' => 'Activée',
            ])
            ->add('schedule', CronScheduleType::class, [
                'dayOfWeek' => $options['dayOfWeek'],
                'dayOfMonth' => $options['dayOfMonth'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CronJob::class,
            'dayOfWeek' => false,
            'dayOfMonth' => false,
        ]);
    }
}

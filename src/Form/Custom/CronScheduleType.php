<?php

namespace App\Form\Custom;

use App\DTO\CronSchedule;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CronScheduleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['dayOfWeek']) {
            $builder
                ->add('dayOfWeek', ChoiceType::class, [
                    'choices' => self::createDayOfWeekChoices(),
                ])
            ;
        }

        if ($options['dayOfMonth']) {
            $builder
                ->add('dayOfMonth', ChoiceType::class, [
                    'choices' => self::createDayOfMonthChoices(),
                ])
            ;
        }

        $builder
            ->add('hour', ChoiceType::class, [
                'choices' => self::createHourChoices(),
            ])
            ->add('minute', ChoiceType::class, [
                'choices' => self::createMinuteChoices(),
            ])
            ->addModelTransformer(new class implements DataTransformerInterface
            {
                public function transform($scheduleString)
                {
                    return CronSchedule::createFromString($scheduleString);
                }

                public function reverseTransform($cronSchedule)
                {
                    return $cronSchedule->__toString();
                }
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CronSchedule::class,
            'dayOfWeek' => false,
            'dayOfMonth' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'cron_schedule';
    }

    private static function createDayOfWeekChoices(): array
    {
        return [
            'Lundi' => 1,
            'Mardi' => 2,
            'Mercredi' => 3,
            'Jeudi' => 4,
            'Vendredi' => 5,
            'Samedi' => 6,
            'Dimanche' => 0,
        ];
    }

    private static function createDayOfMonthChoices(): array
    {
        $days = [];

        for ($i = 1; $i <= 31; ++$i) {
            $days[$i] = $i;
        }

        return $days;
    }

    private static function createHourChoices(): array
    {
        $hours = [];

        for ($i = 0; $i <= 23; ++$i) {
            $hours[$i] = $i;
        }

        return $hours;
    }

    private static function createMinuteChoices(): array
    {
        $minutes = [];

        for ($i = 0; $i < 60; $i += 5) {
            $minutes[$i] = $i;
        }

        return $minutes;
    }
}

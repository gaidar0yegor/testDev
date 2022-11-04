<?php

namespace App\Service\Evenement;

use Symfony\Contracts\Translation\TranslatorInterface;

class EvenementReminderChoices
{
    public static function getChoices(TranslatorInterface $translator): array
    {
        return [
            ['value' => 0, 'label' => $translator->trans('AT_TIME')],
            ['value' => 10, 'label' => $translator->trans('10_MINUTES_BEFORE')],
            ['value' => 60, 'label' => $translator->trans('1_HOUR_BEFORE')],
            ['value' => 60 * 6, 'label' => $translator->trans('6_HOURS_BEFORE')],
            ['value' => 60 * 12, 'label' => $translator->trans('12_HOURS_BEFORE')],
            ['value' => 60 * 24, 'label' => $translator->trans('1_DAY_BEFORE')],
            ['value' => 60 * 24 * 2, 'label' => $translator->trans('2_DAY_BEFORE')],
            ['value' => 60 * 24 * 3, 'label' => $translator->trans('3_DAY_BEFORE')],
            ['value' => 60 * 24 * 4, 'label' => $translator->trans('4_DAY_BEFORE')],
            ['value' => 60 * 24 * 7, 'label' => $translator->trans('1_WEEK_BEFORE')],
            ['value' => 60 * 24 * 14, 'label' => $translator->trans('2_WEEK_BEFORE')],
        ];
    }
}

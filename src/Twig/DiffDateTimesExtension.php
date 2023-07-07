<?php

namespace App\Twig;

use App\MultiSociete\UserContext;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class DiffDateTimesExtension extends AbstractExtension
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('diffDateTimes', [$this, 'diffDateTimes']),
            new TwigFunction('diffDays', [$this, 'diffDays']),
        ];
    }

    public function diffDays(\DateTime $date1, \DateTime $date2 = null)
    {
        $date2 = $date2 === null ? new \DateTime() : $date2;

        $diff = round(($date1->getTimestamp() - $date2->getTimestamp()) / (60 * 60 * 24));

        return $diff;
    }

    public function diffDateTimes(\DateTime $date1, \DateTime $date2 = null)
    {
        if ($date2 === null){
            $date1 = $date1->format('Y-m-d H:i:s');
            $date2 = date("Y-m-d H:i:s");
        } else{
            $date1 = $date1->format('Y-m-d H:i:s');
            $date2 = $date2->format('Y-m-d H:i:s');
        }

        $date1 = strtotime($date1);
        $date2 = strtotime($date2);

        $diff = abs($date2 - $date1);
        $years = floor($diff / (365*60*60*24));
        if ($years > 0){
            return $this->translator->trans('n_years', [ 'n' => $years ]);
        }
        $months = floor(($diff - $years * 365*60*60*24)
            / (30*60*60*24));
        if ($months > 0){
            return $this->translator->trans('n_months', [ 'n' => $months ]);
        }

        $days = floor(($diff - $years * 365*60*60*24 -
                $months*30*60*60*24)/ (60*60*24));
        if ($days > 0){
            return $this->translator->trans('n_days', [ 'n' => $days ]);
        }

        $hours = floor(($diff - $years * 365*60*60*24
                - $months*30*60*60*24 - $days*60*60*24)
            / (60*60));
        if ($hours > 0){
            return $this->translator->trans('n_hours', [ 'n' => $hours ]);
        }

        $minutes = floor(($diff - $years * 365*60*60*24
                - $months*30*60*60*24 - $days*60*60*24
                - $hours*60*60)/ 60);
        if ($minutes > 0){
            return $this->translator->trans('n_minutes', [ 'n' => $minutes ]);
        }

        $seconds = floor(($diff - $years * 365*60*60*24
            - $months*30*60*60*24 - $days*60*60*24
            - $hours*60*60 - $minutes*60));
        if ($seconds > 0){
            return $this->translator->trans('n_seconds', [ 'n' => $seconds ]);
        }

        return $this->translator->trans('now');
    }
}

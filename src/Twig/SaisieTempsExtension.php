<?php

namespace App\Twig;

use App\Service\DateMonthService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SaisieTempsExtension extends AbstractExtension
{
    private DateMonthService $dateMonthService;

    public function __construct(DateMonthService $dateMonthService)
    {
        $this->dateMonthService = $dateMonthService;
    }

    public function getFilters(): array
    {
        return [
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('currentOrPreviousMonth', [$this, 'currentOrPreviousMonth']),
        ];
    }

    public function currentOrPreviousMonth(\DateTimeInterface $month): bool
    {
        $now = new \DateTime();
        $month = $this->dateMonthService->normalize($month);

        return $month < $now;
    }
}

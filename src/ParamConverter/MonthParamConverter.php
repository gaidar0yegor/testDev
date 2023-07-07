<?php

namespace App\ParamConverter;

use App\Exception\MonthOutOfRangeException;
use App\Service\DateMonthService;
use DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MonthParamConverter implements ParamConverterInterface
{
    private DateMonthService $dateMonthService;

    public function __construct(DateMonthService $dateMonthService)
    {
        $this->dateMonthService = $dateMonthService;
    }

    public function apply(Request $request, ParamConverter $configuration)
    {
        if (!$request->attributes->has('year') || !$request->attributes->has('month')) {
            return false;
        }

        $year = $request->get('year');
        $month = $request->get('month');

        if ((null === $year) xor (null === $month)) {
            throw new NotFoundHttpException("Le mois et l'année doivent tous les deux être définis.");
        }

        try {
            $date = $this->dateMonthService->getMonthFromYearAndMonth($year, $month);
        } catch (MonthOutOfRangeException $e) {
            throw new NotFoundHttpException("$year-$month n'est pas un mois valide.", $e);
        }

        $request->attributes->set($configuration->getName(), $date);

        return true;
    }

    public function supports(ParamConverter $configuration)
    {
        return $configuration->getClass() === DateTime::class;
    }
}

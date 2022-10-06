<?php

namespace App\Controller\CorpApp\API;

use App\Entity\Cra;
use App\Entity\TempsPasse;
use App\Listener\UseCache;
use App\MultiSociete\UserContext;
use App\Service\CraService;
use App\Service\DateMonthService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api/temps")
 */
class TempsController extends AbstractController
{
    private DateMonthService $dateMonthService;

    public function __construct(DateMonthService $dateMonthService)
    {
        $this->dateMonthService = $dateMonthService;
    }

    /**
     * @Route(
     *   "/{year}/{month}",
     *   methods={"GET"},
     *   requirements={"year"="\d{4}", "month"="\d{2}"},
     *   name="api_get_temps"
     * )
     *
     * @UseCache()
     * @Cache(maxage="300", vary={"Cookie"})
     */
    public function getCraMonthly(
        DateTime $month,
        CraService $craService,
        NormalizerInterface $normalizer,
        UserContext $userContext
    ): JsonResponse {
        if ($month > new \DateTime()) {
            return new JsonResponse([
                'message' => 'Impossible de saisir les temps passés dans le futur.',
            ], 404);
        }

        $cra = $craService->loadCraForUser($userContext->getSocieteUser(), $month);

        $normalizedCra = $normalizer->normalize($cra, null, [
            AbstractNormalizer::GROUPS => ['saisieTemps'],
        ]);

        foreach ($normalizedCra['tempsPasses'] as &$tempsPasse) {
            $tempsPasse['pourcentage'] = $tempsPasse['pourcentages'][0];
            unset($tempsPasse['pourcentages']);
        }

        $normalizedCra = $craService->calculatePourcentageMinimun($cra, $normalizedCra, $month);

        $normalizedCra['isUserBelongingToSociete'] = $this->dateMonthService->isUserBelongingToSocieteByDate($userContext->getSocieteUser(),$month);

        return new JsonResponse($normalizedCra);
    }

    /**
     * @Route(
     *   "/{year}/{month}",
     *   methods={"POST"},
     *   requirements={"year"="\d{4}", "month"="\d{2}"},
     *   name="api_post_temps"
     * )
     */
    public function postCraMonthly(
        Request $request,
        DateTime $month,
        CraService $craService,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        UserContext $userContext
    ): JsonResponse {
        if ($month > new \DateTime()) {
            return new JsonResponse([
                'message' => 'Impossible de saisir les temps passés dans le futur.',
            ], 404);
        }

        $cra = $craService->loadCraForUser($userContext->getSocieteUser(), $month);
        $submittedTempsPasses = json_decode($request->getContent());

        foreach ($submittedTempsPasses as $submittedTempsPasse) {
            list($projetId, $pourcentage) = $submittedTempsPasse;

            foreach ($cra->getTempsPasses()->toArray() as $tempsPasse) {
                if ($tempsPasse->getProjet()->getId() === $projetId) {
                    $tempsPasse->setPourcentage($pourcentage);
                    break;
                }
            }
        }

        if ($errorResponse = self::validateCra($cra, $validator)) {
            return $errorResponse;
        }

        $cra->setTempsPassesModifiedAt(new DateTime());

        $em->persist($cra);
        $em->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * @Route(
     *   "/weekly/{year}/{month}/{day}",
     *   methods={"GET"},
     *   requirements={"year"="\d{4}", "month"="\d{2}", "day"="\d{2}"},
     *   name="api_get_temps_weekly"
     * )
     *
     * @UseCache()
     * @Cache(maxage="300", vary={"Cookie"})
     */
    public function getCraWeekly(
        DateTime $month,
        string $day,
        CraService $craService,
        NormalizerInterface $normalizer,
        UserContext $userContext
    ): JsonResponse {
        $month->setDate($month->format('Y'), $month->format('m'), $day);

        if ($month > new \DateTime()) {
            return new JsonResponse([
                'message' => 'Impossible de saisir les temps passés dans le futur.',
            ], 404);
        }

        $cra = $craService->loadCraForUser($userContext->getSocieteUser(), $month);

        $normalizedCra = $normalizer->normalize($cra, null, [
            AbstractNormalizer::GROUPS => ['saisieTemps'],
        ]);

        foreach ($normalizedCra['tempsPasses'] as &$tempsPasse) {
            if (count($tempsPasse['pourcentages']) > 1) {
                $tempsPasse['pourcentage'] = $tempsPasse['pourcentages'][intval($month->format('d')) - 1];
            } else {
                $tempsPasse['pourcentage'] = $tempsPasse['pourcentages'][0];
            }

            unset($tempsPasse['pourcentages']);
        }

        $normalizedCra = $craService->calculatePourcentageMinimun($cra, $normalizedCra, $month);

        $normalizedCra['isUserBelongingToSociete'] = $this->dateMonthService->isUserBelongingToSocieteByDate($userContext->getSocieteUser(), $month,false);

        return new JsonResponse($normalizedCra);
    }

    /**
     * @Route(
     *   "/weekly/{year}/{month}/{day}",
     *   methods={"POST"},
     *   requirements={"year"="\d{4}", "month"="\d{2}", "day"="\d{2}"},
     *   name="api_post_temps_weekly"
     * )
     */
    public function postCraWeekly(
        Request $request,
        DateTime $month,
        string $day,
        CraService $craService,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        DateMonthService $dateMonthService,
        UserContext $userContext
    ): JsonResponse {
        $month->setDate($month->format('Y'), $month->format('m'), $day);

        if ($month > new \DateTime()) {
            return new JsonResponse([
                'message' => 'Impossible de saisir les temps passés dans le futur.',
            ], 404);
        }

        $cra = $craService->loadCraForUser($userContext->getSocieteUser(), $month);
        $submittedTempsPasses = json_decode($request->getContent());

        $rest = self::fillWeeklyCra($cra, $submittedTempsPasses, $month);

        // Fill days in next month if week overlaps next month
        if ($rest > 0) {
            $nextMonth = $dateMonthService->getNextMonth($month);

            $nextCra = $craService->loadCraForUser(
                $userContext->getSocieteUser(),
                $nextMonth
            );

            self::fillWeeklyCra($nextCra, $submittedTempsPasses, $nextMonth, $rest);
            $em->persist($nextCra);
        }

        if ($errorResponse = self::validateCra($cra, $validator)) {
            return $errorResponse;
        }

        $cra->setTempsPassesModifiedAt(new DateTime());

        $em->persist($cra);
        $em->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * @Route(
     *   "/daily/{year}/{month}/{day}",
     *   methods={"GET"},
     *   requirements={"year"="\d{4}", "month"="\d{2}", "day"="\d{2}"},
     *   name="api_get_temps_daily"
     * )
     *
     * @UseCache()
     * @Cache(maxage="300", vary={"Cookie"})
     */
    public function getCraDaily(
        DateTime $month,
        string $day,
        CraService $craService,
        NormalizerInterface $normalizer,
        UserContext $userContext
    ): JsonResponse {
        $month->setDate($month->format('Y'), $month->format('m'), $day);

        if ($month > new \DateTime()) {
            return new JsonResponse([
                'message' => 'Impossible de saisir les temps passés dans le futur.',
            ], 404);
        }

        $cra = $craService->loadCraForUser($userContext->getSocieteUser(), $month);

        $normalizedCra = $normalizer->normalize($cra, null, [
            AbstractNormalizer::GROUPS => ['saisieTemps'],
        ]);

        foreach ($normalizedCra['tempsPasses'] as &$tempsPasse) {
            if (count($tempsPasse['pourcentages']) > 1) {
                $tempsPasse['pourcentages'] = array_slice($tempsPasse['pourcentages'], intval($month->format('d')) - 1, 7);
            } else {
                $tempsPasse['pourcentages'] = array_fill(0, 7, $tempsPasse['pourcentages'][0]);
            }
        }

        $normalizedCra['isUserBelongingToSociete'] = $this->dateMonthService->isUserBelongingToSocieteByDate($userContext->getSocieteUser(), $month, false);

        return new JsonResponse($normalizedCra);
    }

    /**
     * @Route(
     *   "/daily/{year}/{month}/{day}",
     *   methods={"POST"},
     *   requirements={"year"="\d{4}", "month"="\d{2}", "day"="\d{2}"},
     *   name="api_post_temps_daily"
     * )
     */
    public function postCraDaily(
        Request $request,
        DateTime $month,
        string $day,
        CraService $craService,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        DateMonthService $dateMonthService,
        UserContext $userContext
    ): JsonResponse {
        $month->setDate($month->format('Y'), $month->format('m'), $day);

        if ($month > new \DateTime()) {
            return new JsonResponse([
                'message' => 'Impossible de saisir les temps passés dans le futur.',
            ], 404);
        }

        $cra = $craService->loadCraForUser($userContext->getSocieteUser(), $month);
        $submittedTempsPasses = json_decode($request->getContent());
        $rest = self::fillWeeklyCra($cra, $submittedTempsPasses, $month);

        // Fill days in next month if week overlaps next month
        if ($rest > 0) {
            $nextMonth = $dateMonthService->getNextMonth($month);

            $nextCra = $craService->loadCraForUser(
                $userContext->getSocieteUser(),
                $nextMonth
            );

            self::fillWeeklyCra($nextCra, $submittedTempsPasses, $nextMonth, $rest);
            $em->persist($nextCra);
        }

        if ($errorResponse = self::validateCra($cra, $validator)) {
            return $errorResponse;
        }

        $cra->setTempsPassesModifiedAt(new DateTime());

        $em->persist($cra);
        $em->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    private static function validateCra(Cra $cra, ValidatorInterface $validator): ?JsonResponse
    {
        $violations = $validator->validate($cra);

        if (0 === count($violations)) {
            return null;
        }

        return new JsonResponse([
            'message' => join(' ; ', array_map(function (ConstraintViolationInterface $violation) {
                return $violation->getMessage();
            }, iterator_to_array($violations))),
        ], JsonResponse::HTTP_BAD_REQUEST);
    }

    private static function fillWeeklyCra(Cra $cra, array $submittedTempsPasses, DateTime $date, int $days = 7): int
    {
        foreach ($submittedTempsPasses as $submittedTempsPasse) {
            list($projetId, $pourcentage) = $submittedTempsPasse;

            foreach ($cra->getTempsPasses()->toArray() as $tempsPasse) {
                if ($tempsPasse->getProjet()->getId() === $projetId) {
                    $rest = self::fillWeeklyTempsPasse($tempsPasse, $date, $pourcentage, $days);
                    break;
                }
            }
        }

        return $rest;
    }

    /**
     * Fill temps passe pourcentages of a full week in a month,
     * from the monday $date.
     *
     * @param TempsPasse $tempsPasse
     * @param DateTime $date
     * @param int|int[] $pourcentages Pourcentage to fill for the week.
     *                                If an int is provided, then the week will be filled with the same value.
     * @param int $days = 7
     *
     * @return int Number of days still to fill in next month, if week overlap with next month.
     */
    private static function fillWeeklyTempsPasse(TempsPasse $tempsPasse, DateTime $date, $pourcentages, int $days = 7): int
    {
        if (!is_array($pourcentages)) {
            $pourcentages = [$pourcentages];
        }

        $dayIndex = intval($date->format('d')) - 1;
        $newPourcentages = $tempsPasse->getPourcentages();

        if (1 === count($newPourcentages)) {
            $newPourcentages = array_fill(0, intval($date->format('t')), $newPourcentages[0]);
        }

        for ($i = $dayIndex; $i < $dayIndex + $days && $i < count($newPourcentages); ++$i) {
            $newPourcentages[$i] = $pourcentages[($i - $dayIndex) % count($pourcentages)];
        }

        $tempsPasse->setPourcentages($newPourcentages);

        return max(0, 7 - (count($newPourcentages) - $dayIndex));
    }
}

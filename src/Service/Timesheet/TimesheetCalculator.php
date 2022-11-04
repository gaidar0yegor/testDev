<?php

namespace App\Service\Timesheet;

use App\DTO\FilterTimesheet;
use App\DTO\Timesheet;
use App\DTO\TimesheetProjet;
use App\Entity\Cra;
use App\Entity\ProjetParticipant;
use App\Entity\Societe;
use App\Entity\SocieteUser;
use App\Entity\TempsPasse;
use App\Exception\TimesheetException;
use App\Repository\EvenementParticipantRepository;
use App\Service\CraService;
use App\Service\DateMonthService;

class TimesheetCalculator
{
    private UserContributingProjetRepositoryInterface $participationRepository;

    private UserMonthCraRepositoryInterface $craRepository;

    private EvenementParticipantRepository $evenementParticipantRepository;

    private CraService $craService;

    private DateMonthService $dateMonthService;

    public function __construct(
        UserContributingProjetRepositoryInterface $participationRepository,
        UserMonthCraRepositoryInterface $craRepository,
        EvenementParticipantRepository $evenementParticipantRepository,
        CraService $craService,
        DateMonthService $dateMonthService
    ) {
        $this->participationRepository = $participationRepository;
        $this->craRepository = $craRepository;
        $this->evenementParticipantRepository = $evenementParticipantRepository;
        $this->craService = $craService;
        $this->dateMonthService = $dateMonthService;
    }

    /**
     * @param ProjetParticipant[] $projetParticipants
     * @param Cra $cra
     * @return TimesheetProjet[]
     * @throws TimesheetException
     */
    public function generateTimesheetProjets(array $projetParticipants, Cra $cra): array
    {
        $societeUser = $cra->getSocieteUser();
        $this->craService->uncheckJoursNotBelongingToSociete($cra, $societeUser);

        $tempsPasses = [];
        foreach ($projetParticipants as $projetParticipant){
            $tempsPasse = $this->getTempsPassesOnProjet($cra, $projetParticipant);
            if (null !== $tempsPasse) {
                $tempsPasses[$projetParticipant->getProjet()->getId()] = $tempsPasse;
            }
        }

        if ($societeUser->getSociete()->getTimesheetGranularity() === Societe::GRANULARITY_DAILY){
            $workedHoursProjets = $this->generateWorkedHoursPerDayForDaiy($cra, $tempsPasses);
        } else {
            $workedHoursProjets = $this->generateWorkedHoursPerDay($cra, $tempsPasses);
        }

        $timesheetProjets = [];
        foreach ($projetParticipants as $projetParticipant){
            if (isset($tempsPasses[$projetParticipant->getProjet()->getId()])){
                $timesheetProjets[] = new TimesheetProjet(
                    $projetParticipant,
                    $tempsPasses[$projetParticipant->getProjet()->getId()],
                    $workedHoursProjets[$projetParticipant->getProjet()->getId()]
                );
            }
        }

        return $timesheetProjets;
    }

    /**
     * @param SocieteUser $societeUser
     * @param \DateTime $month
     * @return Timesheet
     * @throws TimesheetException
     */
    public function generateTimesheet(SocieteUser $societeUser, \DateTime $month): Timesheet
    {
        $month = $this->dateMonthService->normalize($month);
        $cra = $this->craRepository->findCraByUserAndMois($societeUser, $month);

        if (null === $cra) {
            throw new TimesheetException(sprintf(
                'Impossible de générer la feuille de temps :'
                .' l\'utilisateur "%s" n\'a pas rempli ses jours de congés du mois %s.',
                $societeUser->getUser()->getEmail(),
                $month->format('Y/m')
            ));
        }

        $participations = $this->participationRepository->findProjetsContributingUser($cra->getSocieteUser());

        $projetParticipants = [];
        foreach ($participations as $participation) {
            if (!$this->dateMonthService->isProjetActiveInMonth($participation->getProjet(), $month)) {
                continue;
            }

            $projetParticipants[] = $participation;
        }

        $timesheetProjets = $this->generateTimesheetProjets($projetParticipants, $cra);
        return new Timesheet(
            $cra,
            $timesheetProjets
        );
    }

    /**
     * @return Timesheet[]
     */
    public function generateMultipleTimesheets(FilterTimesheet $filter): array
    {
        $from = $this->dateMonthService->normalize($filter->getFrom());
        $to = $this->dateMonthService->normalize($filter->getTo());

        $timesheets = [];

        foreach ($filter->getUsers() as $user) {
            for ($month = $from; $month <= $to; $month = $this->dateMonthService->getNextMonth($month)) {
                try {
                    $timesheets[] = $this->generateTimesheet($user, $month);
                } catch (TimesheetException $e) {
                }
            }
        }

        return $timesheets;
    }

    /**
     * @param Cra $cra
     * @param TempsPasse[] $tempsPasses
     * @return array
     * @throws TimesheetException
     */
    public function generateWorkedHoursPerDay(Cra $cra, array $tempsPasses): array
    {
        $societeUser = $cra->getSocieteUser();
        $heuresParJours = Timesheet::getUserHeuresParJours($societeUser);
        $this->craService->uncheckJoursNotBelongingToSociete($cra, $societeUser);

        $presenceJours = $cra->getJours();
        $totalHeurePasse = array_sum($presenceJours) * $heuresParJours;

        $projetIds = [];
        $tempsPasseProjets = [];

        foreach ($tempsPasses as $tempsPasse){
            $projetIds[] = $tempsPasse->getProjet()->getId();
            $tempsPasseProjets[$tempsPasse->getProjet()->getId()] = ($totalHeurePasse * (array_sum($tempsPasse->getPourcentages()) / count($tempsPasse->getPourcentages()))) / 100;
        }
        arsort($tempsPasseProjets);

        $eventsHeuresPasseProjets = $this->evenementParticipantRepository->getHeuresBySocieteUserByMonth($societeUser, $projetIds, $cra->getMois());

        foreach ($projetIds as $projetId){
            if (!array_key_exists($projetId, $eventsHeuresPasseProjets)){
                $eventsHeuresPasseProjets[$projetId] = array_fill(0, count($cra->getJours()), null);
            }
        }

        // premier passage remplissage par demi journée
        foreach ($tempsPasseProjets as $projetId => $tempsPasseProjet){
            foreach ($eventsHeuresPasseProjets[$projetId] as $jour => $heuresJour){
                $sumHeuresProjet = array_sum($eventsHeuresPasseProjets[$projetId]);
                if (null === $heuresJour){
                    $sumHeuresJour = array_sum(array_column($eventsHeuresPasseProjets,$jour));
                    $maxHeuresJour = $presenceJours[$jour] * $heuresParJours;
                    if (
                        ($tempsPasseProjet >= $sumHeuresProjet) &&
                        ($maxHeuresJour / 2) + $sumHeuresJour <= $maxHeuresJour
                    ){
                        $eventsHeuresPasseProjets[$projetId][$jour] = min($tempsPasseProjet - $sumHeuresProjet, ($maxHeuresJour / 2));
                    } else {
                        $eventsHeuresPasseProjets[$projetId][$jour] = 0;
                    }
                }
            }
        }


        // completer les heures par projet
        foreach ($tempsPasseProjets as $projetId => $tempsPasseProjet){
            if (array_sum($eventsHeuresPasseProjets[$projetId]) === $tempsPasseProjet){
                continue;
            }

            foreach ($eventsHeuresPasseProjets[$projetId] as $jour => $heuresJour){
                $sumHeuresProjet = array_sum($eventsHeuresPasseProjets[$projetId]);
                $sumHeuresJour = array_sum(array_column($eventsHeuresPasseProjets, $jour));
                $maxHeuresJour = $presenceJours[$jour] * $heuresParJours;

                if (($tempsPasseProjet > $sumHeuresProjet) && ($maxHeuresJour > $sumHeuresJour)){
                    $eventsHeuresPasseProjets[$projetId][$jour] += min(
                        $tempsPasseProjet - $sumHeuresProjet,
                        $maxHeuresJour - $sumHeuresJour
                    );
                }
            }
        }

        return $eventsHeuresPasseProjets;
    }

    /**
     * @return float[] Lisser les heures de travaille par jours sur un mois
     */
    private function generateWorkedHoursPerDayForDaiy(Cra $cra, array $tempsPasses): array
    {
        $societeUser = $cra->getSocieteUser();
        $heuresParJours = Timesheet::getUserHeuresParJours($societeUser);

        $this->craService->uncheckJoursNotBelongingToSociete($cra, $societeUser);

        $workedHoursProjets = [];
        foreach ($tempsPasses as $tempsPasse){
            $workedHoursProjets[$tempsPasse->getProjet()->getId()] = array_map(
                function (float $presenceJour, int $key) use ($heuresParJours, $tempsPasse) {
                    $day = (new \DateTime($tempsPasse->getCra()->getMois()->format('d-m-Y')))->modify("+$key days");

                    if (!$tempsPasse->getProjet()->isProjetActiveInDate($day)) {
                        return 0.0;
                    }

                    return ($heuresParJours * $presenceJour * $tempsPasse->getPourcentage($key)) / 100.0;
                },
                $cra->getJours(),
                array_keys($cra->getJours())
            );
        }

        return $workedHoursProjets;
    }

    private function getTempsPassesOnProjet(Cra $cra, ProjetParticipant $projetParticipant): ?TempsPasse
    {
        $tempsPasses = $cra->getTempsPasses()->filter(function (TempsPasse $tempsPasse) use ($projetParticipant) {
            return $tempsPasse->getProjet() === $projetParticipant->getProjet();
        });

        if (0 === $tempsPasses->count()) {
            return null;
        }

        return $tempsPasses->first();
    }
}

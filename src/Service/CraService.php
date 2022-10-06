<?php

namespace App\Service;

use App\DTO\Timesheet;
use App\Entity\Cra;
use App\Entity\EvenementParticipant;
use App\Entity\Projet;
use App\Entity\Societe;
use App\Entity\SocieteUser;
use App\Entity\SocieteUserPeriod;
use App\Entity\TempsPasse;
use App\Repository\CraRepository;
use App\Repository\EvenementParticipantRepository;
use App\Repository\ProjetRepository;
use App\Security\Role\RoleProjet;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

class CraService
{
    private $dateMonthService;

    private $evenementService;

    private $craRepository;

    private $projetRepository;

    private $evenementParticipantRepository;

    private $joursFeriesCalculator;

    private $em;

    public function __construct(
        DateMonthService $dateMonthService,
        EvenementService $evenementService,
        CraRepository $craRepository,
        ProjetRepository $projetRepository,
        EvenementParticipantRepository $evenementParticipantRepository,
        JoursFeriesCalculator $joursFeriesCalculator,
        EntityManagerInterface $em
    ) {
        $this->dateMonthService = $dateMonthService;
        $this->evenementService = $evenementService;
        $this->craRepository = $craRepository;
        $this->projetRepository = $projetRepository;
        $this->evenementParticipantRepository = $evenementParticipantRepository;
        $this->joursFeriesCalculator = $joursFeriesCalculator;
        $this->em = $em;
    }

    /**
     * Créer un cra par défaut pour le mois donné,
     * avec les weekend et jours férié déjà décochés.
     */
    public function createDefaultCra(\DateTime $month): Cra
    {
        $cra = new Cra();

        $month = $this->dateMonthService->normalize($month);
        $days = array_fill(0, $month->format('t'), 1);

        $cra
            ->setMois($month)
            ->setJours($days)
        ;

        $this->uncheckWeekEnds($cra);
        $this->uncheckJoursFeries($cra);

        return $cra;
    }

    public function loadCraForUser(SocieteUser $societeUser, \DateTime $month): Cra
    {
        $month = $this->dateMonthService->normalize($month);
        $cra = $this->craRepository->findCraByUserAndMois($societeUser, $month);

        if (null === $cra) {
            $cra = $this->createDefaultCra($month);
            $cra->setSocieteUser($societeUser);

            $this->uncheckJoursNotBelongingToSociete($cra, $societeUser);
        }

        $this->prefillTempsPasses($cra);

        return $cra;
    }

    /**
     * Retourner le nombre des projets actives et aux quels l'user participe
     */
    public function getActiveProjectsByUserAndMois(SocieteUser $societeUser, \DateTime $mois): array
    {
        $userProjets = $this
            ->projetRepository
            ->findAllForUser($societeUser, RoleProjet::CONTRIBUTEUR, $mois)
        ;
        $activeProjects = [];

        foreach ($userProjets as $userProjet) {
            if (!$this->dateMonthService->isSuspendedProjectByMonth($userProjet, $mois)){
                array_push($activeProjects,$userProjet);
            }
        }

        return $activeProjects;
    }

    /**
     * Initialize la liste des temps passés du Cra.
     */
    public function prefillTempsPasses(Cra $cra): void
    {
        $userProjets = $this
            ->projetRepository
            ->findAllForUser($cra->getSocieteUser(), RoleProjet::CONTRIBUTEUR, $cra->getMois())
        ;

        foreach ($userProjets as $userProjet) {
            $isSuspendedProject = $this->dateMonthService->isSuspendedProjectByMonth($userProjet, $cra->getMois());
            if ($isSuspendedProject){
                $this->deleteTempsPasseByProjet($cra,$userProjet);
                continue;
            }
            if ($this->craContainsProjet($cra, $userProjet)) {
                continue;
            }

            $tempsPasse = new TempsPasse();

            $tempsPasse
                ->setProjet($userProjet)
                ->setPourcentage(0)
            ;

            $cra->addTempsPass($tempsPasse);
        }
    }

    /**
     * Calculer le pourcentage minimum par projet : % du nombre d'heures passées dans les réunions, évenements ,...
     */
    public function calculatePourcentageMinimun(Cra $cra, array $normalizedCra, \DateTime $month): array
    {
        if (null === $cra->getMois()){
            return $normalizedCra;
        }

        $evenementParticipants = new ArrayCollection(
            $this->evenementParticipantRepository->findBySocieteUserByMonth($cra->getSocieteUser(), $month)
        );
        $heuresParJours = Timesheet::getUserHeuresParJours($cra->getSocieteUser());

        if ($cra->getSociete()->getTimesheetGranularity() === Societe::GRANULARITY_MONTHLY){
            $maxHeureWork = array_sum(array_map(function($el) use ($heuresParJours) { return (float)$el * $heuresParJours; }, $cra->getJours()));
        } elseif ($cra->getSociete()->getTimesheetGranularity() === Societe::GRANULARITY_WEEKLY){
            $maxHeureWork = array_sum(array_map(function($el) use ($heuresParJours) { return (float)$el * $heuresParJours; }, array_slice($cra->getJours(), (int)$month->format('d') - 1, 7)));
        } else{
            return $normalizedCra;
        }


        foreach ($normalizedCra['tempsPasses'] as &$tempsPasse){
            $tempsPasse['pourcentageMin'] = 0.0;

            $evenementProjetParticipants = $evenementParticipants->filter(
                function (EvenementParticipant $evenementParticipant) use ($tempsPasse) {
                    return  $evenementParticipant->getProjet()->getId() === $tempsPasse['projet']['id'];
                }
            );

            if ($evenementProjetParticipants->count() === 0) continue;

            $tempsPerEvenement = 0;
            foreach ($evenementProjetParticipants as $evenementProjetParticipant){
                if (null === $evenementProjetParticipant->getHeures()){
                    continue;
                }
                if (false === array_key_exists($month->format('Y-m'),$evenementProjetParticipant->getHeures())) {
                    continue;
                }

                $heures = $evenementProjetParticipant->getHeures()[$month->format('Y-m')];

                if ($cra->getSociete()->getTimesheetGranularity() === Societe::GRANULARITY_MONTHLY){
                    $tempsPerEvenement += array_sum($heures);
                } elseif ($cra->getSociete()->getTimesheetGranularity() === Societe::GRANULARITY_WEEKLY){
                    $tempsPerEvenement += array_sum(array_slice($heures, (int)$month->format('d') - 1, 7));
                }
            }
            $tempsPasse['pourcentageMin'] = ceil(($tempsPerEvenement / $maxHeureWork) * 100);
        }

        return $normalizedCra;
    }

    /**
     * Décoche les week end.
     */
    public function uncheckWeekEnds(Cra $cra): void
    {
        $craYear = $cra->getMois()->format('Y');
        $craMonth = $cra->getMois()->format('m');
        $days = $cra->getJours();

        for ($i = 0; $i < count($cra->getJours()); ++$i) {
            $currentDate = \DateTime::createFromFormat('Y-n-j', "$craYear-$craMonth-".($i + 1));

            if (intval($currentDate->format('N')) >= 6) {
                $days[$i] = 0;
            }
        }

        $cra->setJours($days);
    }

    /**
     * Décoche les jours fériés.
     */
    public function uncheckJoursFeries(Cra $cra): void
    {
        $craYear = $cra->getMois()->format('Y');
        $craMonth = $cra->getMois()->format('m');
        $joursFeries = $this->joursFeriesCalculator->calcJoursFeries($craYear, $craMonth);
        $days = $cra->getJours();

        foreach ($joursFeries as $jourFerie) {
            $days[intval($jourFerie->format('j')) - 1] = 0;
        }

        $cra->setJours($days);
    }

    /**
     * Décoche les jours où $societeUser n'est pas dans la société.
     */
    public function uncheckJoursNotBelongingToSociete(Cra $cra, SocieteUser $societeUser): void
    {
        $joursCra = $cra->getJours();
        $boolCra = array_fill(0, count($cra->getJours()), false);
        $craMonth = $this->dateMonthService->normalize($cra->getMois());

        foreach ($societeUser->getSocieteUserPeriods() as $societeUserPeriod){
            $boolCra = $this->handleUserPeriod($craMonth, $boolCra, $societeUserPeriod);
        }

        foreach ($boolCra as $key => $value){
            if ($value === false){
                $joursCra[$key] = 0;
            }
        }

        $cra->setJours($joursCra);

    }

    public function handleUserPeriod(\DateTime $craMonth, array $boolCra, SocieteUserPeriod $societeUserPeriod): array
    {
        $dateEntry = $societeUserPeriod->getDateEntry();
        $dateLeave = $societeUserPeriod->getDateLeave();

        $dateEntryMois = $dateEntry ? $dateEntry->format('Y-m') : null;
        $dateLeaveMois = $dateLeave ? $dateLeave->format('Y-m') : null;
        $craMonthMois = $craMonth->format('Y-m');

        if (null === $dateEntryMois && null === $dateLeaveMois){
            return $boolCra;
        }

        if ($craMonthMois > $dateEntryMois && (null === $dateLeaveMois || (null !== $dateLeaveMois && $craMonthMois < $dateLeaveMois))){
            $boolCra = array_fill(0, count($boolCra), true);
        }

        if ($craMonthMois == $dateEntryMois && (null === $dateLeaveMois || (null !== $dateLeaveMois && $craMonthMois < $dateLeaveMois))){
            $from = intval($dateEntry->format('j')) - 1;

            for ($i = $from; $i < count($boolCra); ++$i) {
                $boolCra[$i] = true;
            }
        }

        if (null !== $dateLeaveMois && $craMonthMois == $dateLeaveMois){
            if ($craMonthMois > $dateEntryMois){
                $to = intval($dateLeave->format('j')) - 1;

                for ($i = 0; $i <= $to; $i++) {
                    $boolCra[$i] = true;
                }
            }

            if ($craMonthMois == $dateEntryMois){
                $from = intval($dateEntry->format('j')) - 1;
                $to = intval($dateLeave->format('j')) - 1;

                for ($i = $from; $i <= $to; $i++) {
                    $boolCra[$i] = true;
                }
            }
        }

        return $boolCra;
    }

    public function getFirstNotValidMonth(SocieteUser $societeUser)
    {
        $firstPeriod = $societeUser->getSocieteUserPeriods()->first();
        $notValidMois = null;
        if ($firstPeriod instanceof SocieteUserPeriod && null !== $firstPeriod->getDateEntry()){
            $craValidMois = $this->craRepository->findValidMoisByUser($societeUser);
            $month = (new \DateTime(date('01-' . $firstPeriod->getDateEntry()->format('m-Y'))))->getTimestamp();
            $end = (new \DateTime(date('01-m-Y')))->getTimestamp();

            while($month < $end)
            {
                if (
                    count($this->getActiveProjectsByUserAndMois($societeUser, (new \DateTime())->setTimestamp($month))) &&
                    $this->dateMonthService->isUserBelongingToSocieteByDate($societeUser,(new \DateTime())->setTimestamp($month)) &&
                    !in_array((new \DateTime())->setTimestamp($month), $craValidMois)
                ){
                    $notValidMois = (new \DateTime())->setTimestamp($month)->format('Y-m');
                    break;
                }

                $month = strtotime("+1 month", $month);
            }
        }

        return $notValidMois;
    }

    /**
     * @param TempsPasse[] $tempsPasses Liste de temps passés à verifier si un est lié au $projet.
     * @param Projet $projet
     *
     * @return bool Si Un des temps passé correspond au projet.
     */
    private function craContainsProjet(Cra $cra, Projet $projet): bool
    {
        foreach ($cra->getTempsPasses() as $tempsPasse) {
            if ($tempsPasse->getProjet() === $projet) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param TempsPasse[] $tempsPasses Liste de temps passés pour supprimer qui est lié au projet un s'il existe
     * @param Projet $projet
     */
    private function deleteTempsPasseByProjet(Cra $cra, Projet $projet): void
    {
        foreach ($cra->getTempsPasses() as $tempsPasse) {
            if ($tempsPasse->getProjet()->getId() === $projet->getId()) {
                $cra->removeTempsPass($tempsPasse);
                $this->em->remove($tempsPasse);

                $cra->setTempsPassesModifiedAt(null);
                $this->em->persist($cra);
                $this->em->flush();
            }
        }
    }
}

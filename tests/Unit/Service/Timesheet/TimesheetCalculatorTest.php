<?php

namespace App\Tests\Unit\Service;

use App\Entity\Cra;
use App\Entity\Projet;
use App\Entity\ProjetParticipant;
use App\Entity\TempsPasse;
use App\Entity\User;
use App\Role;
use App\Service\DateMonthService;
use App\Service\Timesheet\TimesheetCalculator;
use App\Service\Timesheet\UserContributingProjetRepositoryInterface;
use App\Service\Timesheet\UserMonthCraRepositoryInterface;
use PHPUnit\Framework\TestCase;

class TimesheetCalculatorTest extends TestCase
{
    private DateMonthService $dateMonthService;

    private User $user;
    private Projet $projet;
    private TempsPasse $tempsPasse;
    private ProjetParticipant $participation;
    private Cra $cra;
    private \DateTimeInterface $mois;

    public function __construct()
    {
        parent::__construct();

        $this->dateMonthService = new DateMonthService();
    }

    private function prepareBasicSample()
    {
        $this->user = new User();
        $this->projet = new Projet();
        $this->tempsPasse = new TempsPasse();

        $this->mois = new \DateTime('01-01-2020');
        $this->dateMonthService->normalize($this->mois);

        $this->participation = new ProjetParticipant();
        $this->participation
            ->setUser($this->user)
            ->setProjet($this->projet)
            ->setRole(Role::CONTRIBUTEUR)
        ;

        $this->cra = new Cra();
        $this->cra
            ->setUser($this->user)
            ->setMois($this->mois)
            ->setJours([
                      0, 1, 1, 0, 0,
                1, 1, 1, 1, 1, 0, 0,
                1, 1, 1, 1, 1, 0, 0,
                1, 1, 1, 1, 1, 0, 0,
                1, 1, 1, 1, 0.5,
            ])
        ;

        $this->tempsPasse
            ->setMois($this->mois)
            ->setPourcentage(50)
            ->setProjet($this->projet)
        ;

        $this->user
            ->addTempsPass($this->tempsPasse)
            ->setHeuresParJours(8.0)
        ;
    }

    private function createTimesheetCalculator(array $participations = null)
    {
        if (null === $participations) {
            $participations = [$this->participation];
        }

        return new TimesheetCalculator(
            new class ($participations) implements UserContributingProjetRepositoryInterface
            {
                public function __construct(array $participations)
                {
                    $this->participations = $participations;
                }

                public function findProjetsContributingUser(User $user): array
                {
                    return $this->participations;
                }
            },
            new class ($this->cra) implements UserMonthCraRepositoryInterface
            {
                public function __construct(Cra $cra)
                {
                    $this->cra = $cra;
                }

                public function findCraByUserAndMois(User $user, \DateTimeInterface $mois): ?Cra
                {
                    return $this->cra;
                }
            },
            $this->dateMonthService
        );
    }

    public function testGenerateTimesheetProjet()
    {
        $this->prepareBasicSample();

        $timesheetCalculator = $this->createTimesheetCalculator();

        $timesheetProjet = $timesheetCalculator->generateTimesheetProjet($this->participation, $this->cra);

        $this->assertEquals(
            31,
            count($timesheetProjet->getWorkedHours()),
            'Le cra contient les 31 jours de janvier'
        );

        $this->assertEquals(
            21.5 * 8.0 * 0.5,
            $timesheetProjet->getTotalWorkedHours(),
            'User a travaillé au total 21.5 jours de 8 heures, à 50% sur le projet'
        );

        $this->assertEquals(
            [
                      0, 4, 4, 0, 0,
                4, 4, 4, 4, 4, 0, 0,
                4, 4, 4, 4, 4, 0, 0,
                4, 4, 4, 4, 4, 0, 0,
                4, 4, 4, 4, 2,
            ],
            $timesheetProjet->getWorkedHours(),
            'Les heures de la feuille de temps sont les bonnes'
        );
    }

    public function testGenerateTimesheetProjetWithDateDebut()
    {
        $this->prepareBasicSample();

        $this->projet->setDateDebut(new \DateTime('15-01-2020'));

        $timesheetCalculator = $this->createTimesheetCalculator();

        $timesheetProjet = $timesheetCalculator->generateTimesheetProjet($this->participation, $this->cra);

        $this->assertEquals(
            31,
            count($timesheetProjet->getWorkedHours()),
            'Le cra contient les 31 jours de janvier'
        );

        $this->assertEquals(
            12.5 * 8.0 * 0.5,
            $timesheetProjet->getTotalWorkedHours(),
            'User a travaillé au total 12.5 jours de 8 heures, à 50% sur le projet'
        );

        $this->assertEquals(
            [
                      0, 0, 0, 0, 0,
                0, 0, 0, 0, 0, 0, 0,
                0, 0, 4, 4, 4, 0, 0,
                4, 4, 4, 4, 4, 0, 0,
                4, 4, 4, 4, 2,
            ],
            $timesheetProjet->getWorkedHours(),
            'Les heures de la feuille de temps sont les bonnes'
        );
    }

    public function testGenerateTimesheetProjetWithDateFin()
    {
        $this->prepareBasicSample();

        $this->projet->setDateFin(new \DateTime('15-01-2020'));

        $timesheetCalculator = $this->createTimesheetCalculator();

        $timesheetProjet = $timesheetCalculator->generateTimesheetProjet($this->participation, $this->cra);

        $this->assertEquals(
            31,
            count($timesheetProjet->getWorkedHours()),
            'Le cra contient les 31 jours de janvier'
        );

        $this->assertEquals(
            10.0 * 8.0 * 0.5,
            $timesheetProjet->getTotalWorkedHours(),
            'User a travaillé au total 10 jours de 8 heures, à 50% sur le projet'
        );

        $this->assertEquals(
            [
                      0, 4, 4, 0, 0,
                4, 4, 4, 4, 4, 0, 0,
                4, 4, 4, 0, 0, 0, 0,
                0, 0, 0, 0, 0, 0, 0,
                0, 0, 0, 0, 0,
            ],
            $timesheetProjet->getWorkedHours(),
            'Les heures de la feuille de temps sont les bonnes'
        );
    }

    public function testGenerateTimesheetProjetWithDateDebutEtFin()
    {
        $this->prepareBasicSample();

        $this->projet
            ->setDateDebut(new \DateTime('10-01-2020'))
            ->setDateFin(new \DateTime('20-01-2020'))
        ;

        $timesheetCalculator = $this->createTimesheetCalculator();

        $timesheetProjet = $timesheetCalculator->generateTimesheetProjet($this->participation, $this->cra);

        $this->assertEquals(
            31,
            count($timesheetProjet->getWorkedHours()),
            'Le cra contient les 31 jours de janvier'
        );

        $this->assertEquals(
            7.0 * 8.0 * 0.5,
            $timesheetProjet->getTotalWorkedHours(),
            'User a travaillé au total 7 jours de 8 heures, à 50% sur le projet'
        );

        $this->assertEquals(
            [
                      0, 0, 0, 0, 0,
                0, 0, 0, 0, 4, 0, 0,
                4, 4, 4, 4, 4, 0, 0,
                4, 0, 0, 0, 0, 0, 0,
                0, 0, 0, 0, 0,
            ],
            $timesheetProjet->getWorkedHours(),
            'Les heures de la feuille de temps sont les bonnes'
        );
    }

    public function testGenerateTimesheet()
    {
        $this->prepareBasicSample();

        $timesheetCalculator = $this->createTimesheetCalculator();

        $timesheet = $timesheetCalculator->generateTimesheet($this->user, $this->mois);

        $this->assertEquals(
            1,
            count($timesheet->getProjets()),
            'La feuille de temps contient le seul projet'
        );

        $timesheetProjet = $timesheet->getProjets()[0];

        $this->assertEquals(
            31,
            count($timesheetProjet->getWorkedHours()),
            'Le cra contient les 31 jours de janvier'
        );

        $this->assertEquals(
            21.5 * 8.0 * 0.5,
            $timesheetProjet->getTotalWorkedHours(),
            'User a travaillé au total 21.5 jours de 8 heures, à 50% sur le projet'
        );

        $this->assertEquals(
            [
                      0, 4, 4, 0, 0,
                4, 4, 4, 4, 4, 0, 0,
                4, 4, 4, 4, 4, 0, 0,
                4, 4, 4, 4, 4, 0, 0,
                4, 4, 4, 4, 2,
            ],
            $timesheetProjet->getWorkedHours(),
            'Les heures de la feuille de temps sont les bonnes'
        );
    }

    public function testGenerateTimesheetDoesNotDisplayProjetFinished()
    {
        $this->prepareBasicSample();

        $projetEnded = new Projet();
        $participationProjetEnded = new ProjetParticipant();

        $projetEnded->setDateFin(new \DateTime('01-01-2018'));

        $participationProjetEnded
            ->setProjet($projetEnded)
            ->setUser($this->user)
            ->setRole(Role::CONTRIBUTEUR)
        ;

        $timesheetCalculator = $this->createTimesheetCalculator([
            $this->participation,
            $participationProjetEnded,
        ]);

        $timesheet = $timesheetCalculator->generateTimesheet($this->user, $this->mois);

        $this->assertEquals(
            1,
            count($timesheet->getProjets()),
            'La feuille de temps ne contient pas le projet terminé, seulement le projet actif'
        );
    }

    public function testGenerateTimesheetDoesNotDisplayProjetNotYetStarted()
    {
        $this->prepareBasicSample();

        $projetEnded = new Projet();
        $participationProjetEnded = new ProjetParticipant();

        $projetEnded->setDateDebut(new \DateTime('01-01-2022'));

        $participationProjetEnded
            ->setProjet($projetEnded)
            ->setUser($this->user)
            ->setRole(Role::CONTRIBUTEUR)
        ;

        $timesheetCalculator = $this->createTimesheetCalculator([
            $this->participation,
            $participationProjetEnded,
        ]);

        $timesheet = $timesheetCalculator->generateTimesheet($this->user, $this->mois);

        $this->assertEquals(
            1,
            count($timesheet->getProjets()),
            'La feuille de temps ne contient pas le projet pas encore commencé, seulement le projet actif'
        );
    }

    public function testGenerateTimesheetDisplaysProjetStartingInCurrentMonth()
    {
        $this->prepareBasicSample();

        $projetEnded = new Projet();
        $participationProjetEnded = new ProjetParticipant();

        $projetEnded->setDateDebut(new \DateTime('15-01-2020'));

        $participationProjetEnded
            ->setProjet($projetEnded)
            ->setUser($this->user)
            ->setRole(Role::CONTRIBUTEUR)
        ;

        $timesheetCalculator = $this->createTimesheetCalculator([
            $this->participation,
            $participationProjetEnded,
        ]);

        $timesheet = $timesheetCalculator->generateTimesheet($this->user, $this->mois);

        $this->assertEquals(
            2,
            count($timesheet->getProjets()),
            'La feuille de temps contient le projet qui commence le même mois'
        );
    }

    public function testGenerateTimesheetDisplaysProjetFinishingThisMonth()
    {
        $this->prepareBasicSample();

        $projetEnded = new Projet();
        $participationProjetEnded = new ProjetParticipant();

        $projetEnded->setDateFin(new \DateTime('15-01-2020'));

        $participationProjetEnded
            ->setProjet($projetEnded)
            ->setUser($this->user)
            ->setRole(Role::CONTRIBUTEUR)
        ;

        $timesheetCalculator = $this->createTimesheetCalculator([
            $this->participation,
            $participationProjetEnded,
        ]);

        $timesheet = $timesheetCalculator->generateTimesheet($this->user, $this->mois);

        $this->assertEquals(
            2,
            count($timesheet->getProjets()),
            'La feuille de temps contient le projet qui finit le même mois'
        );
    }
}

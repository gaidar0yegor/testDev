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

    private function createTimesheetCalculator()
    {
        return new TimesheetCalculator(
            new class ($this->projet) implements UserContributingProjetRepositoryInterface
            {
                public function __construct(Projet $projet)
                {
                    $this->projet = $projet;
                }

                public function findProjetsContributingUser(User $user): array
                {
                    return [$this->projet];
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
}

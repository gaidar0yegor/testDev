<?php

namespace App\Service;

use App\Command\NotificationCommand\NotificationCreateFaitMarquantCommand;
use App\Command\NotificationCommand\NotificationLatestFaitMarquantCommand;
use App\Command\NotificationCommand\NotificationSaisieTempsCommand;
use App\DTO\CronSchedule;
use App\DTO\SocieteNotifications;
use App\Entity\Societe;
use App\HasSocieteInterface;
use App\Repository\CronJobRepository;
use Cron\CronBundle\Entity\CronJob;
use Doctrine\ORM\EntityManagerInterface;

class SocieteNotificationsService
{
    private CronJobRepository $cronJobRepository;

    private EntityManagerInterface $em;

    public function __construct(CronJobRepository $cronJobRepository, EntityManagerInterface $em)
    {
        $this->cronJobRepository = $cronJobRepository;
        $this->em = $em;
    }

    public function createInitialSocieteNotifications(Societe $societe): SocieteNotifications
    {
        $societeId = $societe->getId();
        $societeNotifications = new SocieteNotifications();

        $societeNotifications->setCreerFaitsMarquants(
            (new CronJob())
                ->setDescription('Rappel pour créer les faits marquants')
                ->setCommand(NotificationCreateFaitMarquantCommand::$defaultName.' --societe '.$societeId)
                ->setName(SocieteNotifications::CREER_FAITS_MARQUANTS.'-societe-'.$societeId)
                ->setEnabled(false)
                ->setSchedule(CronSchedule::everyWeek(5, 12, 0)->__toString())
        );

        $societeNotifications->setDerniersFaitsMarquants(
            (new CronJob())
                ->setDescription('Liste des derniers faits marquants ajoutés')
                ->setCommand(NotificationLatestFaitMarquantCommand::$defaultName.' --societe '.$societeId)
                ->setName(SocieteNotifications::DERNIERS_FAITS_MARQUANTS.'-societe-'.$societeId)
                ->setEnabled(false)
                ->setSchedule(CronSchedule::everyWeek(1, 12, 0)->__toString())
        );

        $societeNotifications->setSaisieTemps(
            (new CronJob())
                ->setDescription('Rappel pour saisir nos temps et absences')
                ->setCommand(NotificationSaisieTempsCommand::$defaultName.' --societe '.$societeId)
                ->setName(SocieteNotifications::SAISIE_TEMPS.'-societe-'.$societeId)
                ->setEnabled(false)
                ->setSchedule(CronSchedule::everyMonth(28, 12, 0)->__toString())
        );

        return $societeNotifications;
    }

    public function loadForSociete(HasSocieteInterface $entity): SocieteNotifications
    {
        $societe = $entity->getSociete();
        $societeId = $societe->getId();
        $societeNotifications = $this->createInitialSocieteNotifications($societe);
        $cronJobs = $this->cronJobRepository->findAllSameSociete($entity);

        foreach ($cronJobs as $cronJob) {
            switch ($cronJob->getName()) {
                case SocieteNotifications::CREER_FAITS_MARQUANTS.'-societe-'.$societeId:
                    $societeNotifications->setCreerFaitsMarquants($cronJob);
                    break;

                case SocieteNotifications::DERNIERS_FAITS_MARQUANTS.'-societe-'.$societeId:
                    $societeNotifications->setDerniersFaitsMarquants($cronJob);
                    break;

                case SocieteNotifications::SAISIE_TEMPS.'-societe-'.$societeId:
                    $societeNotifications->setSaisieTemps($cronJob);
                    break;
            }
        }

        $societeNotifications->setSmsEnabled($societe->getSmsEnabled());

        return $societeNotifications;
    }

    public function persistAll(Societe $societe, SocieteNotifications $societeNotifications)
    {
        $this->em->persist($societeNotifications->getCreerFaitsMarquants());
        $this->em->persist($societeNotifications->getDerniersFaitsMarquants());
        $this->em->persist($societeNotifications->getSaisieTemps());

        $societe->setSmsEnabled($societeNotifications->getSmsEnabled());
        $this->em->persist($societe);
    }
}

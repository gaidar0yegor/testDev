<?php

namespace App\Command\NotificationCommand;

use App\Entity\Societe;
use App\Exception\RdiException;
use App\Repository\SocieteRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

abstract class AbstractNotificationCommand extends Command
{
    private SocieteRepository $societeRepository;

    protected function configure()
    {
        $this
            ->addOption('societe', null, InputOption::VALUE_REQUIRED, 'Id de la société dont les utilisateurs doivent êtres notifiés.')
        ;
    }

    public function setSocieteRepository(SocieteRepository $societeRepository): void
    {
        $this->societeRepository = $societeRepository;
    }

    protected function findCommandSociete(InputInterface $input): Societe
    {
        $societeId = $input->getOption('societe');

        if (null === $societeId) {
            throw new RdiException('Un id de société doit être fourni dans la commande avec "--societe N"');
        }

        $societe = $this->societeRepository->find($societeId);

        if (null === $societe) {
            throw new RdiException("La société $societeId n'existe pas");
        }

        return $societe;
    }
}

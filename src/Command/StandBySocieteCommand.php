<?php

namespace App\Command;

use App\Entity\Societe;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class StandBySocieteCommand extends Command
{
    protected static $defaultName = 'app:mettre-en-veille-societes-desactivees';

    private EntityManagerInterface $em;
    private EventDispatcherInterface $dispatcher;

    public function __construct(EntityManagerInterface $em, EventDispatcherInterface $dispatcher)
    {
        parent::__construct();

        $this->em = $em;
        $this->dispatcher = $dispatcher;
    }

    protected function configure()
    {
        $this
            ->setDescription('Mettre en veille les sociétés désactivés après une période.')
            ->addArgument('nbrMonths',InputOption::VALUE_REQUIRED, 'Société désactivée depuis X mois :', 12)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $nbrMonths = (int)$input->getArgument('nbrMonths');

        $societes = $this->em->getRepository(Societe::class)->findToMettreEnVeille($nbrMonths);

        $sum = 0;
        foreach ($societes as $societe){
            $societe->setEnabled(false);
            $societe->setOnStandBy(true);
            $this->em->persist($societe);
            $sum++;
        }

        $this->em->flush();

        if ($sum === 0){
            $io->success('Aucun société est mise en veille.');
        } else {
            $io->success($sum . ' société(s) est mise en veille avec succés !');
        }

        return 0;
    }
}

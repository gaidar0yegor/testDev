<?php

namespace App\Command;

use App\Entity\Societe;
use App\File\DefaultAvatarGenerator;
use App\File\FileResponseFactory;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class OrganizeFilesIntoFoldersCommand extends Command
{
    protected static $defaultName = 'app:file-system:organize-files';
    protected static $defaultDescription = 'Organiser les fichiers dans des dossier : /societe_id/projet_id/';

    private EntityManagerInterface $em;
    private FilesystemInterface $storage;

    public function __construct(
        EntityManagerInterface $em,
        FilesystemInterface $projectFilesStorage
    )
    {
        parent::__construct();

        $this->em = $em;
        $this->storage = $projectFilesStorage;
    }

    protected function configure()
    {
        $this
            ->setDescription(self::$defaultDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->info('Loop all FichierProjet of all Societe/Projet');

        $societes = $this->em->getRepository(Societe::class)->findAll();

        foreach ($societes as $societe) {
            foreach ($societe->getProjets() as $projet) {
                foreach ($projet->getFichierProjets() as $fichierProjet) {
                    if ($this->storage->has($fichierProjet->getFichier()->getNomMd5())) {
                        $this->storage->rename($fichierProjet->getFichier()->getNomMd5(), $fichierProjet->getRelativeFilePath());
                    }
                }
            }
        }

        $this->em->flush();

        $io->info('all FichierProjet are organized');

        return Command::SUCCESS;
    }
}

<?php

namespace App\LicenseGeneration\Command;

use App\License\Factory\LicenseFactoryInterface;
use App\License\LicenseService;
use App\LicenseGeneration\LicenseGeneration;
use App\Repository\SocieteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateLicensesCommand extends Command
{
    private LicenseFactoryInterface $licenseFactory;

    private SocieteRepository $societeRepository;

    private LicenseGeneration $licenseGeneration;

    private LicenseService $licenseService;

    private EntityManagerInterface $entityManager;

    public function __construct(
        LicenseFactoryInterface $licenseFactory,
        SocieteRepository $societeRepository,
        LicenseGeneration $licenseGeneration,
        LicenseService $licenseService,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct();

        $this->licenseFactory = $licenseFactory;
        $this->societeRepository = $societeRepository;
        $this->licenseGeneration = $licenseGeneration;
        $this->licenseService = $licenseService;
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
            ->setDescription('Génère des licenses pour toutes les sociétés.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $societes = $this->societeRepository->findAll();

        foreach ($societes as $societe) {
            $license = $this->licenseFactory->createLicense($societe);

            $io->info(sprintf(
                'Generating license "%s" for %s (%s)...',
                $license->getName(),
                $societe->getRaisonSociale(),
                $societe->getUuid()
            ));

            $licenseContent = $this->licenseGeneration->generateLicenseFile($license);

            $this->licenseService->storeLicense($licenseContent);

            $societe->setProductKey($license->getProductKey());
            $this->entityManager->persist($societe);
        }

        $this->entityManager->flush();

        return 0;
    }
}

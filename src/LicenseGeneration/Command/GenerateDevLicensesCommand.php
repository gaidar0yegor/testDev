<?php

namespace App\LicenseGeneration\Command;

use App\License\DTO\License;
use App\License\LicenseService;
use App\LicenseGeneration\LicenseGeneration;
use App\Repository\SocieteRepository;
use DateTime;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateDevLicensesCommand extends Command
{
    protected static $defaultName = 'app:license-generation:generate-dev-licenses';

    private SocieteRepository $societeRepository;

    private LicenseGeneration $licenseGeneration;

    private LicenseService $licenseService;

    public function __construct(
        SocieteRepository $societeRepository,
        LicenseGeneration $licenseGeneration,
        LicenseService $licenseService
    ) {
        parent::__construct();

        $this->societeRepository = $societeRepository;
        $this->licenseGeneration = $licenseGeneration;
        $this->licenseService = $licenseService;
    }

    protected function configure()
    {
        $this
            ->setDescription('Génère des licenses illimitées pour toutes les sociétés.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $societes = $this->societeRepository->findAll();
        $expirationDate = (new DateTime())->modify('+6 months');

        foreach ($societes as $societe) {
            $io->info(sprintf(
                'Generating license for %s (%s)...',
                $societe->getRaisonSociale(),
                $societe->getUuid()
            ));

            $license = License::createUnlimitedLicense($societe, $expirationDate);
            $licenseContent = $this->licenseGeneration->generateLicenseFile($license);

            $this->licenseService->storeLicense($licenseContent);
        }

        return 0;
    }
}

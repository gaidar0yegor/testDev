<?php

namespace App\License\Command;

use App\License\Exception\DecryptionException;
use App\License\LicenseService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ReadLicenseCommand extends Command
{
    protected static $defaultName = 'app:license:read';

    private LicenseService $licenseService;

    public function __construct(LicenseService $licenseService)
    {
        parent::__construct();

        $this->licenseService = $licenseService;
    }

    protected function configure()
    {
        $this
            ->setDescription('Déchiffre et affiche les données brutes d\'une license.')
            ->addArgument('licenseFilename', InputArgument::REQUIRED, 'Chemin vers le fichier de license.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $licenseFilename = $input->getArgument('licenseFilename');
        $licenseContent = file_get_contents($licenseFilename);

        try {
            $license = $this->licenseService->parseLicenseContent($licenseContent);
        } catch (DecryptionException $e) {
            $io->error($e->getMessage());
            return 1;
        }

        $tableData = [
            '# Société',
            ['Raison sociale' => $license->getSociete()->getRaisonSociale()],
            ['Uuid' => $license->getSociete()->getUuid()],

            new TableSeparator(),
            '# License',
            ['Nom' => $license->getName()],
            ['Date d\'expiration' => $license->getExpirationDate()->format('j F Y')],
            $license->getDescription() ?: '-- pas de description --',

            new TableSeparator(),
            '# Quotas',
        ];

        foreach ($license->getQuotas() as $quotaName => $quotaValue) {
            $tableData[] = [$quotaName => $quotaValue];
        }

        $io->definitionList(...$tableData);

        return 0;
    }
}

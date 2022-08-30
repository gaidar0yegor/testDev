<?php

namespace App\Command;

use App\Entity\Societe;
use App\License\Factory\StarterLicenseFactory;
use App\License\LicenseService;
use App\LicenseGeneration\LicenseGeneration;
use App\Notification\Event\TryOfferExpiredNotification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CheckExpiredTryOfferLicenseCommand extends Command
{
    protected static $defaultName = 'app:check-expired-try-offer-license';

    private LicenseService $licenseService;
    private LicenseGeneration $licenseGeneration;
    private StarterLicenseFactory $starterLicenseFactory;
    private EntityManagerInterface $em;
    private EventDispatcherInterface $dispatcher;

    public function __construct(
        LicenseService $licenseService,
        LicenseGeneration $licenseGeneration,
        StarterLicenseFactory $starterLicenseFactory,
        EntityManagerInterface $em,
        EventDispatcherInterface $dispatcher
    )
    {
        parent::__construct();

        $this->licenseService = $licenseService;
        $this->licenseGeneration = $licenseGeneration;
        $this->starterLicenseFactory = $starterLicenseFactory;
        $this->em = $em;
        $this->dispatcher = $dispatcher;
    }

    protected function configure()
    {
        $this
            ->setDescription('Vérifier et transformer les licences d\'essai expirées')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $societes = $this->em->getRepository(Societe::class)->findAll();

        foreach ($societes as $societe) {
            if ($this->licenseService->checkHasTryLicenseExpired($societe)) {

                $io->info('Offre d\'essai expirée pour la société : ' . $societe->getRaisonSociale());

                $license = $this->starterLicenseFactory->createLicense($societe);

                $licenseContent = $this->licenseGeneration->generateLicenseFile($license);

                $this->licenseService->storeLicense($licenseContent);

                $societe->setProductKey($license->getProductKey());

                $this->em->persist($societe);

                $io->info('Une license Starter est générée pour la société : ' . $societe->getRaisonSociale());

                $this->dispatcher->dispatch(new TryOfferExpiredNotification($societe));

                $io->info('Une notification par mail est envoyée à tous les administrateurs de la société : ' . $societe->getRaisonSociale());
            }
        }

        $this->em->flush();

        $io->success('Toutes les licences ont été vérifées.');

        return Command::SUCCESS;
    }
}

<?php

namespace App\License\Command;

use App\License\Decryption;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DownloadPublicKeyCommand extends Command
{
    protected static $defaultName = 'app:license:download-public-key';

    private Decryption $decryption;

    public function __construct(Decryption $decryption)
    {
        parent::__construct();

        $this->decryption = $decryption;
    }

    protected function configure()
    {
        $this
            ->setDescription('Vérifie la présence de la clé publique, et la télécharge sinon.')
            ->addOption('force', 'f', InputOption::VALUE_OPTIONAL, 'Remove public key if any, and download a fresh one.', false)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $path = $this->decryption->getPublicKeyFilename();
        $url = $this->decryption->getDownloadUrl();

        if ($this->decryption->hasPublicKey()) {
            $io->success("Public key already here, at $path");

            if ($input->hasOption('force')) {
                $io->info('Removing it to download a new one...');
                unlink($path);
            } else {
                $io->info('You can pass option "--force" to re-download public key');
                return 0;
            }
        } else {
            $io->info("Public key not found at $path");
        }

        $io->info("Downloading from $url ...");

        $this->decryption->downloadPublicKey();

        if (!$this->decryption->hasPublicKey()) {
            $io->error('Error while downloading public key from !');
            return 1;
        }

        $io->success("Public key successfully downloaded at $path");

        return 0;
    }
}

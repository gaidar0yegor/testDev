<?php

namespace App\LicenseGeneration\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GeneratePrivateKeyCommand extends Command
{
    protected static $defaultName = 'app:license-generation:generate-private';

    private string $privateKeyFile;

    private string $publicKeyFile;

    public function __construct(string $privateKeyFile, string $publicKeyFile)
    {
        parent::__construct();

        $this->privateKeyFile = $privateKeyFile;
        $this->publicKeyFile = $publicKeyFile;
    }

    protected function configure()
    {
        $this
            ->setDescription('Génère une paire de clé privée/publique, et les stocke dans les dossiers définis en configuration.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $alreadyGenerated = false;

        if (file_exists($this->privateKeyFile)) {
            $io->error("Une clé privée est déjà créée dans $this->privateKeyFile");
            $alreadyGenerated = true;
        }

        if (file_exists($this->publicKeyFile)) {
            $io->error("Une clé publique est déjà créée dans $this->publicKeyFile");
            $alreadyGenerated = true;
        }

        if ($alreadyGenerated) {
            return 1;
        }

        $io->info('Génération d\'une nouvelle paire de clés privée et clé publique...');

        $config = [
            'private_key_bits' => 4096,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];

        // Create the private and public key
        $resource = openssl_pkey_new($config);

        // Extract the private key from $resource to $privateKey
        openssl_pkey_export($resource, $privateKey);

        // Extract the public key from $resource to $publicKey
        $publicKey = openssl_pkey_get_details($resource)['key'];

        if (!file_exists(dirname($this->privateKeyFile))) {
            mkdir(dirname($this->privateKeyFile), 0777, true);
        }

        if (!file_exists(dirname($this->publicKeyFile))) {
            mkdir(dirname($this->publicKeyFile), 0777, true);
        }

        file_put_contents($this->privateKeyFile, $privateKey);
        file_put_contents($this->publicKeyFile, $publicKey);
        chmod($this->privateKeyFile, 0600);
        chmod($this->publicKeyFile, 0644);

        $io->success(join(PHP_EOL, [
            'Private and public keys generated and stored in:',
            $this->privateKeyFile,
            $this->publicKeyFile,
        ]));

        return 0;
    }
}

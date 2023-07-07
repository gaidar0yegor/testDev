<?php

namespace App\Command;

use App\Service\RdiMailer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TestMailCommand extends Command
{
    protected static $defaultName = 'app:test-mail';

    private RdiMailer $mailer;

    public function __construct(RdiMailer $mailer)
    {
        parent::__construct();

        $this->mailer = $mailer;
    }

    protected function configure()
    {
        $this
            ->setDescription('Envoi un email de test à une adresse pour tester la configuration du serveur.')
            ->addArgument('email', InputArgument::REQUIRED, 'Adresse sur laquelle envoyer l\'email')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');

        $this->mailer->sendTestEmail($email);

        $io->success('L\'email de test a été envoyé !');

        return 0;
    }
}

<?php

namespace App\Command;

use App\Entity\User;
use App\Notification\Sms\SmsSender;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TestSmsCommand extends Command
{
    protected static $defaultName = 'app:test-sms';

    private string $smsDsn;

    private SmsSender $smsSender;

    private PhoneNumberUtil $phoneNumberUtil;

    private UrlGeneratorInterface $urlGenerator;

    public function __construct(
        string $smsDsn,
        SmsSender $smsSender,
        PhoneNumberUtil $phoneNumberUtil,
        UrlGeneratorInterface $urlGenerator
    ) {
        parent::__construct();

        $this->smsDsn = $smsDsn;
        $this->smsSender = $smsSender;
        $this->phoneNumberUtil = $phoneNumberUtil;
        $this->urlGenerator = $urlGenerator;
    }

    protected function configure()
    {
        $this
            ->setDescription('Envoi un SMS de test à un numéro pour tester la configuration du serveur.')
            ->addArgument('numero', InputArgument::REQUIRED, 'Numéro sur lequel envoyer le SMS')
            ->addOption('message', null, InputOption::VALUE_OPTIONAL, 'Message du SMS à envoyer')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $numero = $input->getArgument('numero');

        try {
            $phoneNumber = $this->phoneNumberUtil->parse($numero, 'FR');
        } catch (NumberParseException $e) {
            $io->error('Le numéro de téléphone semble invalide : '.$e->getMessage());
            return 1;
        }

        $user = new User();
        $user->setTelephone($phoneNumber);

        $homeUrl = $this->urlGenerator->generate('app_home', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $message = sprintf(
            'Ceci est un SMS de test envoyé depuis RDI-Manager. '
            .'Si vous le recevez, le serveur est bien configuré pour envoyer les SMS RDI-Manager. '
            .'Url absolue de RDI-Manager : %s',
            $homeUrl
        );

        if ($input->getOption('message')) {
            $message = $input->getOption('message');
        }

        $sent = $this->smsSender->sendSms($user->getTelephone(), $message);

        $io->note(sprintf('DSN configuré : "%s".', $this->smsDsn));

        if (!$sent) {
            $io->error('Le SMS n\'a pas été envoyé');
            return 1;
        }

        $io->success('Le SMS de test a été envoyé !');

        return 0;
    }
}

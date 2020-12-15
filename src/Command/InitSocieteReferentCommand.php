<?php

namespace App\Command;

use App\Entity\Societe;
use App\Entity\User;
use App\Service\TokenGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class InitSocieteReferentCommand extends Command
{
    protected static $defaultName = 'app:init-societe-referent';

    private $em;

    private $tokenGenerator;

    private $urlGenerator;

    public function __construct(
        EntityManagerInterface $em,
        TokenGenerator $tokenGenerator,
        UrlGeneratorInterface $urlGenerator
    ) {
        parent::__construct();

        $this->em = $em;
        $this->tokenGenerator = $tokenGenerator;
        $this->urlGenerator = $urlGenerator;
    }

    protected function configure()
    {
        $this
            ->setDescription('Initialise une société et un référent.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Création de votre société et de votre accès référent');

        $societeRaisonSociale = $io->ask(
            'Nom de votre société',
            null,
            function ($s) {
                if (0 === strlen($s)) {
                    throw new \RuntimeException('Le nom de la société ne peut pas être vide');
                }

                return $s;
            }
        );

        $adminEmail = $io->ask(
            'Votre email, ou l\'email du référent (cet email sera utilisé comme nom d\'utilisateur)',
            null,
            function ($s) {
                if (!preg_match('/.+@.+/', $s)) {
                    throw new \RuntimeException('L\'email ne semble pas valide, elle doit être sous la forme ...@...');
                }

                return $s;
            }
        );

        // Initialisation de la société et du référent
        $societe = new Societe();
        $admin = new User();

        $societe->setRaisonSociale($societeRaisonSociale);

        $admin
            ->setEmail($adminEmail)
            ->setSociete($societe)
            ->setRole('ROLE_FO_ADMIN')
        ;

        // Création du lien d'invitation pour que le référent finalise son compte
        $invitationToken = $this->tokenGenerator->generateUrlToken();

        $admin->setInvitationToken($invitationToken);

        $invitationLink = $this->urlGenerator->generate('app_fo_user_finalize_inscription', [
            'token' => $invitationToken,
        ], UrlGeneratorInterface::RELATIVE_PATH);

        $this->em->persist($admin);
        $this->em->persist($societe);
        $this->em->flush();

        $io->success([
            'Votre accès a été initialisé.',
            'Utilisez ce code d\'invitation pour finaliser votre compte :',
            $invitationLink,
        ]);

        $io->note([
            'Ajoutez ce code à la suite de l\'url de votre installation de RDI manager,',
            'par exemple : http://example.tld/invitation/CODE',
        ]);

        return 0;
    }
}

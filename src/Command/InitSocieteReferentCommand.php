<?php

namespace App\Command;

use App\DTO\InitSociete;
use App\Entity\Societe;
use App\Entity\User;
use App\Service\Invitator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class InitSocieteReferentCommand extends Command
{
    protected static $defaultName = 'app:init-societe-referent';

    private EntityManagerInterface $em;

    private Invitator $invitator;

    private UrlGeneratorInterface $urlGenerator;

    public function __construct(
        EntityManagerInterface $em,
        Invitator $invitator,
        UrlGeneratorInterface $urlGenerator
    ) {
        parent::__construct();

        $this->em = $em;
        $this->invitator = $invitator;
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
        $fromUser = new User();
        $fromUser->setPrenom('Eurêka C.I');

        $io = new SymfonyStyle($input, $output);

        $io->title('Création de la société et de l\'administrateur');

        $societeRaisonSociale = $io->ask(
            'Nom de la société',
            null,
            function ($s) {
                if (0 === strlen($s)) {
                    throw new \RuntimeException('Le nom de la société ne peut pas être vide');
                }

                return $s;
            }
        );

        $adminEmail = $io->ask(
            'Email de l\'administrateur de la société (cet email sera utilisé comme nom d\'utilisateur)',
            null,
            function ($s) {
                if (!preg_match('/.+@.+/', $s)) {
                    throw new \RuntimeException('L\'email ne semble pas valide, elle doit être sous la forme ...@...');
                }

                return $s;
            }
        );

        // Initialisation de la société et du référent
        $initSociete = new InitSociete();
        $initSociete
            ->setRaisonSociale($societeRaisonSociale)
            ->setAdminEmail($adminEmail)
        ;

        $societe = $this->invitator->initSociete($initSociete);
        $societe->setCreatedFrom(Societe::CREATED_FROM_COMMAND);
        $this->invitator->check($societe);
        $admin = $societe->getAdmins()->first();

        $send = $io->confirm('Envoyer un email avec le lien d\'invitation à l\'administrateur ?');

        if ($send) {
            $this->invitator->sendInvitation($admin, $fromUser);
        }

        $this->em->persist($societe);
        $this->em->flush();

        $message = [
            'La société et son administrateur ont été initialisés.',
        ];

        if ($send) {
            $message[] = 'Un email a été envoyé à l\'administrateur.';
        } else {
            $message[] = 'Le nouvel administrateur peut aller sur le lien suivant pour finaliser son inscription :';
            $message[] = $this->urlGenerator->generate('corp_app_fo_user_finalize_inscription', [
                'token' => $admin->getInvitationToken(),
            ], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        $io->success($message);

        return 0;
    }
}

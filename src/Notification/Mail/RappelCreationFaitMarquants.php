<?php

namespace App\Notification\Mail;

use App\Entity\SocieteUser;
use App\Notification\Event\RappelCreationFaitMarquantsNotification;
use App\Repository\ProjetRepository;
use App\Repository\SocieteUserRepository;
use App\Security\Role\RoleProjet;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;

class RappelCreationFaitMarquants implements EventSubscriberInterface
{
    private SocieteUserRepository $societeUserRepository;

    private ProjetRepository $projetRepository;

    private MailerInterface $mailer;

    public function __construct(
        SocieteUserRepository $societeUserRepository,
        ProjetRepository $projetRepository,
        MailerInterface $mailer
    ) {
        $this->societeUserRepository = $societeUserRepository;
        $this->projetRepository = $projetRepository;
        $this->mailer = $mailer;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RappelCreationFaitMarquantsNotification::class => 'remindCreateAllUsers',
        ];
    }

    /**
     * @param SocieteUser $societeUser Utilisateur à rappeller de créer ses faits marquants sur les projets dont il contribue.
     *                      Ne fait rien si l'utilisateur ne contribue à aucun projet ce mois ci.
     */
    public function sendReminderFaitMarquant(SocieteUser $societeUser): void
    {
        if (!$societeUser->getSociete()->getEnabled()){
            return;
        }

        if (null === $societeUser->getUser()->getEmail()) {
            return;
        }

        $projets = $this->projetRepository->findAllForUser($societeUser, RoleProjet::CONTRIBUTEUR, new \DateTime());

        if (0 === count($projets)) {
            return;
        }

        $email = (new TemplatedEmail())
            ->to($societeUser->getUser()->getEmail())
            ->subject('Rappel pour créer vos faits marquants sur RDI-Manager')
            ->textTemplate('corp_app/mail/notification_create_fait_marquant.txt.twig')
            ->htmlTemplate('corp_app/mail/notification_create_fait_marquant.html.twig')
            ->context([
                'projets' => $projets,
                'societe' => $societeUser->getSociete(),
            ])
        ;

        $this->mailer->send($email);
    }

    /**
     * @return int Nombre d'utilisateurs qui vont recevoir un email.
     */
    public function remindCreateAllUsers(RappelCreationFaitMarquantsNotification $event): void
    {
        $societeUsers = $this->societeUserRepository->findAllNotifiableUsers(
            'notificationCreateFaitMarquantEnabled',
            $event->getSociete()
        );

        foreach ($societeUsers as $societeUser) {
            $this->sendReminderFaitMarquant($societeUser);
        }
    }
}

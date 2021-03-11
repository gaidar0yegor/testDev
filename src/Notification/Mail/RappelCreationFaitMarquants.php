<?php

namespace App\Notification\Mail;

use App\Entity\User;
use App\Notification\Event\RappelCreationFaitMarquantsNotification;
use App\Repository\ProjetRepository;
use App\Repository\UserRepository;
use App\Role;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;

class RappelCreationFaitMarquants implements EventSubscriberInterface
{
    private UserRepository $userRepository;

    private ProjetRepository $projetRepository;

    private MailerInterface $mailer;

    public function __construct(
        UserRepository $userRepository,
        ProjetRepository $projetRepository,
        MailerInterface $mailer
    ) {
        $this->userRepository = $userRepository;
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
     * @param User $user Utilisateur à rappeller de créer ses faits marquants sur les projets dont il contribue.
     *                      Ne fait rien si l'utilisateur ne contribue à aucun projet ce mois ci.
     */
    public function sendReminderFaitMarquant(User $user): void
    {
        $projets = $this->projetRepository->findAllForUser($user, Role::CONTRIBUTEUR, new \DateTime());

        if (0 === count($projets)) {
            return;
        }

        $email = (new TemplatedEmail())
            ->to($user->getEmail())
            ->subject('Rappel pour créer vos faits marquants sur RDI-Manager')
            ->textTemplate('mail/notification_create_fait_marquant.txt.twig')
            ->htmlTemplate('mail/notification_create_fait_marquant.html.twig')
            ->context([
                'projets' => $projets,
            ])
        ;

        $this->mailer->send($email);
    }

    /**
     * @return int Nombre d'utilisateurs qui vont recevoir un email.
     */
    public function remindCreateAllUsers(RappelCreationFaitMarquantsNotification $event): void
    {
        $users = $this->userRepository->findAllNotifiableUsers(
            $event->getSociete(),
            'notificationCreateFaitMarquantEnabled'
        );

        foreach ($users as $user) {
            $this->sendReminderFaitMarquant($user);
        }
    }
}

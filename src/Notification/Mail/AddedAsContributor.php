<?php

namespace App\Notification\Mail;

use App\Notification\Event\AddedAsContributorNotification;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;

class AddedAsContributor implements EventSubscriberInterface
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AddedAsContributorNotification::class => 'addedAsContributor',
        ];
    }

    public function addedAsContributor(AddedAsContributorNotification $event): void
    {
        $user = $event->getSocieteUser()->getUser();
        $societe = $event->getSocieteUser()->getSociete();

        if (!$societe->getEnabled()){
            return;
        }

        if (null === $user) {
            return;
        }

        if (null === $user->getEmail()) {
            return;
        }

        if (!$user->getNotificationEnabled()) {
            return;
        }

        $email = (new TemplatedEmail())
            ->to($user->getEmail())
            ->subject(sprintf(
                'Vous avez été ajouté sur le projet %s en tant que contributeur',
                $event->getProjet()->getAcronyme()
            ))
            ->htmlTemplate('corp_app/mail/added_as_contributor.html.twig')
            ->textTemplate('corp_app/mail/added_as_contributor.txt.twig')
            ->context([
                'projet' => $event->getProjet(),
                'societe' => $societe,
            ])
        ;

        $this->mailer->send($email);
    }
}

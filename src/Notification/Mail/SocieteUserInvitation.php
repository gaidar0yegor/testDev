<?php

namespace App\Notification\Mail;

use App\Entity\SocieteUser;
use App\Entity\User;
use App\Exception\RdiException;
use App\Notification\Event\SocieteUserInvitationNotification;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;

class SocieteUserInvitation implements EventSubscriberInterface
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SocieteUserInvitationNotification::class => 'onNotification',
        ];
    }

    public function onNotification(SocieteUserInvitationNotification $event): void
    {
        if (null === $event->getSocieteUser()->getInvitationEmail()) {
            return;
        }

        $this->sendInvitationEmail($event->getSocieteUser(), $event->getFrom());
    }

    /**
     * @param SocieteUser $invitedUser User à inviter, doit avoir un token d'invitation
     * @param User $adminUser Référent qui invite l'user, utile pour afficher "XX vous invite..." dans l'email
     */
    public function sendInvitationEmail(SocieteUser $invitedUser, User $fromUser): void
    {
        if (!$invitedUser->getSociete()->getEnabled()){
            return;
        }

        if (null === $invitedUser->getInvitationToken()) {
            throw new RdiException('Cannot send invitation email, this user has no invitation token.');
        }

        $email = (new TemplatedEmail())
            ->to($invitedUser->getInvitationEmail())
            ->subject(sprintf('%s vous invite sur RDI-Manager', $fromUser->getFullname()))
            ->textTemplate('corp_app/mail/invite.txt.twig')
            ->htmlTemplate('corp_app/mail/invite.html.twig')
            ->context([
                'invitedUser' => $invitedUser,
                'fromUser' => $fromUser,
                'societe' => $invitedUser->getSociete(),
            ])
        ;

        $this->mailer->send($email);
    }
}

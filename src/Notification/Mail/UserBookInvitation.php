<?php

namespace App\Notification\Mail;

use App\Entity\LabApp\UserBookInvite;
use App\Entity\SocieteUser;
use App\Entity\User;
use App\Exception\RdiException;
use App\Notification\Event\SocieteUserInvitationNotification;
use App\Notification\Event\UserBookInvitationNotification;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;

class UserBookInvitation implements EventSubscriberInterface
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserBookInvitationNotification::class => 'onNotification',
        ];
    }

    public function onNotification(UserBookInvitationNotification $event): void
    {
        if (null === $event->getUserBookInvite()->getInvitationEmail()) {
            return;
        }

        $this->sendInvitationEmail($event->getUserBookInvite(), $event->getFrom());
    }

    /**
     * @param UserBookInvite $userBookInvite User à inviter, doit avoir un token d'invitation
     * @param User $adminUser Référent qui invite l'user, utile pour afficher "XX vous invite..." dans l'email
     * @throws RdiException
     */
    public function sendInvitationEmail(UserBookInvite $userBookInvite, User $fromUser): void
    {
        if (null === $userBookInvite->getInvitationToken()) {
            throw new RdiException('Cannot send invitation email, this user has no invitation token.');
        }

        $email = (new TemplatedEmail())
            ->to($userBookInvite->getInvitationEmail())
            ->subject(sprintf('%s vous invite sur RDI-Manager', $fromUser->getFullname()))
            ->textTemplate('lab_app/mail/invite.txt.twig')
            ->htmlTemplate('lab_app/mail/invite.html.twig')
            ->context([
                'userBookInvite' => $userBookInvite,
                'fromUser' => $fromUser,
                'labo' => $userBookInvite->getLabo(),
            ])
        ;

        $this->mailer->send($email);
    }
}

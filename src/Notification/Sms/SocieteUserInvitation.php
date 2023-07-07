<?php

namespace App\Notification\Sms;

use App\Entity\SocieteUser;
use App\Entity\User;
use App\Exception\RdiException;
use App\Notification\Event\SocieteUserInvitationNotification;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Twig\Environment as TwigEnvironment;

class SocieteUserInvitation implements EventSubscriberInterface
{
    private TwigEnvironment $twig;

    private SmsSender $smsSender;

    public function __construct(
        TwigEnvironment $twig,
        SmsSender $smsSender
    ) {
        $this->twig = $twig;
        $this->smsSender = $smsSender;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SocieteUserInvitationNotification::class => 'onNotification',
        ];
    }

    public function onNotification(SocieteUserInvitationNotification $event): void
    {
        if (null === $event->getSocieteUser()->getInvitationTelephone()) {
            return;
        }

        $this->sendInvitationSms($event->getSocieteUser(), $event->getFrom());
    }

    /**
     * @param SocieteUser $invitedUser User à inviter, doit avoir un token d'invitation
     * @param User $adminUser Référent qui invite l'user, utile pour afficher "XX vous invite..." dans l'email
     */
    public function sendInvitationSms(SocieteUser $invitedUser, User $fromUser): void
    {
        if (null === $invitedUser->getInvitationToken()) {
            throw new RdiException('Cannot send invitation email, this user has no invitation token.');
        }

        $message = $this->twig->render('corp_app/sms/invite.txt.twig', [
            'invitedUser' => $invitedUser,
            'fromUser' => $fromUser,
        ]);

        $this->smsSender->sendSms($invitedUser->getInvitationTelephone(), $message);
    }
}

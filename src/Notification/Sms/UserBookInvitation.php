<?php

namespace App\Notification\Sms;

use App\Entity\LabApp\UserBookInvite;
use App\Entity\User;
use App\Exception\RdiException;
use App\Notification\Event\UserBookInvitationNotification;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Twig\Environment as TwigEnvironment;

class UserBookInvitation implements EventSubscriberInterface
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
            UserBookInvitationNotification::class => 'onNotification',
        ];
    }

    public function onNotification(UserBookInvitationNotification $event): void
    {
        if (null === $event->getUserBookInvite()->getInvitationTelephone()) {
            return;
        }

        $this->sendInvitationSms($event->getUserBookInvite(), $event->getFrom());
    }

    /**
     * @param UserBookInvite $userBookInvite User à inviter, doit avoir un token d'invitation
     * @param User $adminUser Référent qui invite l'user, utile pour afficher "XX vous invite..." dans l'email
     * @throws RdiException
     */
    public function sendInvitationSms(UserBookInvite $userBookInvite, User $fromUser): void
    {
        if (null === $userBookInvite->getInvitationToken()) {
            throw new RdiException('Cannot send invitation email, this user has no invitation token.');
        }

        $message = $this->twig->render('lab_app/sms/invite.txt.twig', [
            'userBookInvite' => $userBookInvite,
            'fromUser' => $fromUser,
        ]);

        $this->smsSender->sendSms($userBookInvite->getInvitationTelephone(), $message);
    }
}

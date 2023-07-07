<?php

namespace App\Notification\Sms;

use App\Entity\SocieteUser;
use App\Entity\User;
use App\Exception\RdiException;
use App\Notification\Event\ResetPasswordRequestNotification;
use App\Notification\Sms\SmsSender;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Twig\Environment as TwigEnvironment;

class ResetPasswordRequest implements EventSubscriberInterface
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
            ResetPasswordRequestNotification::class => 'onNotification',
        ];
    }

    public function onNotification(ResetPasswordRequestNotification $event): void
    {
        if (null === $event->getUser()->getTelephone()) {
            return;
        }

        $this->sendResetPasswordSms($event->getUser());
    }

    /**
     * @param SocieteUser $invitedUser User à inviter, doit avoir un token d'invitation
     * @param User $adminUser Référent qui invite l'user, utile pour afficher "XX vous invite..." dans l'email
     */
    public function sendResetPasswordSms(User $user): void
    {
        if (!$user->hasResetPasswordToken()) {
            throw new RdiException('Cannot send reset password sms, this user has no reset password token.');
        }

        $message = $this->twig->render('sms/reset_password.txt.twig', [
            'user' => $user,
        ]);

        $this->smsSender->sendSms($user->getTelephone(), $message);
    }
}

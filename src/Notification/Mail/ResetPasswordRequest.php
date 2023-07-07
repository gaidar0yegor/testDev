<?php

namespace App\Notification\Mail;

use App\Entity\User;
use App\Exception\RdiException;
use App\Notification\Event\ResetPasswordRequestNotification;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;

class ResetPasswordRequest implements EventSubscriberInterface
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ResetPasswordRequestNotification::class => 'onNotification',
        ];
    }

    public function onNotification(ResetPasswordRequestNotification $event): void
    {
        if (null === $event->getUser()->getEmail()) {
            return;
        }

        $this->sendResetPasswordEmail($event->getUser());
    }

    public function sendResetPasswordEmail(User $user): void
    {
        if (!$user->hasResetPasswordToken()) {
            throw new RdiException('Cannot send reset password email, this user has no reset password token.');
        }

        $email = (new TemplatedEmail())
            ->to($user->getEmail())
            ->subject(sprintf('Réinitialisation de votre mot de passe RDI-Manager'))
            ->textTemplate('corp_app/mail/reset_password.txt.twig')
            ->htmlTemplate('corp_app/mail/reset_password.html.twig')
            ->context([
                'user' => $user,
            ])
        ;

        $this->mailer->send($email);
    }
}

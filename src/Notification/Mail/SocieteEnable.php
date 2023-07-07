<?php

namespace App\Notification\Mail;

use App\Entity\SocieteUser;
use App\Notification\Event\SocieteDisabledNotification;
use App\Notification\Event\SocieteEnabledNotification;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;

class SocieteEnable implements EventSubscriberInterface
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SocieteEnabledNotification::class => 'societeEnabled',
            SocieteDisabledNotification::class => 'societeDisabled'
        ];
    }

    public function societeEnabled(SocieteEnabledNotification $event): void
    {
        $societe = $event->getSociete();

        $societeAdminEmails = $societe->getAdmins()->map(function (SocieteUser $societeUser){
            return $societeUser->getUser() ? $societeUser->getUser()->getEmail() : '';
        });

        $email = (new TemplatedEmail())
            ->subject('L\'espace RDI-Manager de votre société a été réactivé')
            ->htmlTemplate('corp_app/mail/societe_enabled.html.twig')
            ->textTemplate('corp_app/mail/societe_enabled.txt.twig')
            ->context([
                'societe' => $societe,
            ])
        ;

        $this->sendEmail($email, array_filter($societeAdminEmails->toArray()));
    }

    public function societeDisabled(SocieteDisabledNotification $event): void
    {
        $societe = $event->getSociete();

        $societeAdminEmails = $societe->getAdmins()->map(function (SocieteUser $societeUser){
            return $societeUser->getUser() ? $societeUser->getUser()->getEmail() : '';
        });

        $email = (new TemplatedEmail())
            ->subject('L\'espace RDI-Manager de votre société a été désactivé')
            ->htmlTemplate('corp_app/mail/societe_disabled.html.twig')
            ->textTemplate('corp_app/mail/societe_disabled.txt.twig')
            ->context([
                'societe' => $societe,
            ])
        ;

        $this->sendEmail($email, array_filter($societeAdminEmails->toArray()));
    }

    private function sendEmail(TemplatedEmail $email, array $adminEmails)
    {
        foreach ($adminEmails as $adminEmail){
            $this->mailer->send($email->to($adminEmail));
        }
    }
}

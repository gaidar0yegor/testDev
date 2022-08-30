<?php

namespace App\Notification\Mail;

use App\Entity\SocieteUser;
use App\Notification\Event\TryOfferExpiredNotification;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;

class TryOfferExpired implements EventSubscriberInterface
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            TryOfferExpiredNotification::class => 'TryOfferExpired',
        ];
    }

    public function TryOfferExpired(TryOfferExpiredNotification $event): void
    {
        $societe = $event->getSociete();

        if (!$societe->getEnabled()){
            return;
        }

        $societeAdminEmails = $societe->getAdmins()->map(function (SocieteUser $societeUser){
            return $societeUser->getUser() ? $societeUser->getUser()->getEmail() : '';
        });

        $email = (new TemplatedEmail())
            ->subject('Fin de l’offre d’essai RDI-Manager pour votre société')
            ->htmlTemplate('corp_app/mail/try_offer_expired.html.twig')
            ->textTemplate('corp_app/mail/try_offer_expired.txt.twig')
            ->context([
                'societe' => $societe,
            ])
        ;

        foreach (array_filter($societeAdminEmails->toArray()) as $adminEmail){
            $this->mailer->send($email->to($adminEmail));
        }
    }
}

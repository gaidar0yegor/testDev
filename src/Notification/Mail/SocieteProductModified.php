<?php

namespace App\Notification\Mail;

use App\Entity\SocieteUser;
use App\Notification\Event\SocieteProductModifiedNotification;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;

class SocieteProductModified implements EventSubscriberInterface
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SocieteProductModifiedNotification::class => 'societeProductModified',
        ];
    }

    public function societeProductModified(SocieteProductModifiedNotification $event): void
    {
        $societe = $event->getSociete();
        $oldLicense = $event->getOldLicense();
        $newLicense = $event->getNewLicense();

        $societeAdminEmails = $societe->getAdmins()->map(function (SocieteUser $societeUser){
            return $societeUser->getUser() ? $societeUser->getUser()->getEmail() : '';
        });

        $email = (new TemplatedEmail())
            ->subject('Changement de votre offre RDI-Manager')
            ->htmlTemplate('corp_app/mail/societe_product_modified.html.twig')
            ->textTemplate('corp_app/mail/societe_product_modified.txt.twig')
            ->context([
                'societe' => $societe,
                'oldLicense' => $oldLicense,
                'newLicense' => $newLicense,
            ])
        ;

        foreach (array_filter($societeAdminEmails->toArray()) as $adminEmail){
            $this->mailer->send($email->to($adminEmail));
        }
    }
}

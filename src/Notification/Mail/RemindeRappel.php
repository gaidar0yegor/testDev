<?php

namespace App\Notification\Mail;

use App\Notification\Event\RemindeRappelEvent;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;

class RemindeRappel implements EventSubscriberInterface
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer) {
        $this->mailer = $mailer;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RemindeRappelEvent::class => 'onNotification',
        ];
    }

    public function onNotification(RemindeRappelEvent $event): void
    {
        $rappel = $event->getRappel();

        if (!$rappel->getUser()->getEnabled()){
            return;
        }

        $email = (new TemplatedEmail())
            ->to($rappel->getUser()->getEmail())
            ->subject('Rappel : ' . $rappel->getTitre())
            ->textTemplate('corp_app/mail/reminde_rappel.txt.twig')
            ->htmlTemplate('corp_app/mail/reminde_rappel.html.twig')
            ->context([
                'rappel' => $rappel
            ])
        ;

        $this->mailer->send($email);
    }
}

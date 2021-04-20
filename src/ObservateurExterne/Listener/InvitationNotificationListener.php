<?php

namespace App\ObservateurExterne\Listener;

use App\Notification\Sms\SmsSender;
use App\ObservateurExterne\Notification\InvitationObservateurExterneNotification;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;
use Twig\Environment as Twig;

class InvitationNotificationListener implements EventSubscriberInterface
{
    private Twig $twig;

    private MailerInterface $mailer;

    private SmsSender $smsSender;

    public function __construct(Twig $twig, MailerInterface $mailer, SmsSender $smsSender)
    {
        $this->twig = $twig;
        $this->mailer = $mailer;
        $this->smsSender = $smsSender;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            InvitationObservateurExterneNotification::class => [
                ['onInvitationMail'],
                ['onInvitationSms'],
            ],
        ];
    }

    public function onInvitationMail(InvitationObservateurExterneNotification $event): void
    {
        $emailTo = $event->getProjetObservateurExterne()->getInvitationEmail();

        if (null === $emailTo) {
            return;
        }

        $email = (new TemplatedEmail())
            ->to($emailTo)
            ->subject('Invitation sur le projet '.$event->getProjetObservateurExterne()->getProjet()->getAcronyme())
            ->htmlTemplate('mail/invitation_observateur_externe.html.twig')
            ->textTemplate('mail/invitation_observateur_externe.txt.twig')
            ->context([
                'projetObservateurExterne' => $event->getProjetObservateurExterne(),
            ])
        ;

        $this->mailer->send($email);
    }

    public function onInvitationSms(InvitationObservateurExterneNotification $event): void
    {
        $phoneNumber = $event->getProjetObservateurExterne()->getInvitationTelephone();

        if (null === $phoneNumber) {
            return;
        }

        $smsContent = $this->twig->render('mail/invitation_observateur_externe.txt.twig', [
            'projetObservateurExterne' => $event->getProjetObservateurExterne(),
        ]);

        $this->smsSender->sendSms($phoneNumber, $smsContent);
    }
}

<?php

namespace App\Notification\Mail;

use App\Entity\Cra;
use App\Entity\SocieteUser;
use App\Notification\Event\RappelSaisieTempsNotification;
use App\Repository\SocieteUserRepository;
use App\Service\DateMonthService;
use DateTimeInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;

class RappelSaisieTemps implements EventSubscriberInterface
{
    private SocieteUserRepository $societeUserRepository;

    private DateMonthService $dateMonthService;

    private MailerInterface $mailer;

    public function __construct(
        SocieteUserRepository $societeUserRepository,
        DateMonthService $dateMonthService,
        MailerInterface $mailer
    ) {
        $this->societeUserRepository = $societeUserRepository;
        $this->dateMonthService = $dateMonthService;
        $this->mailer = $mailer;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RappelSaisieTempsNotification::class => 'rappelSaisieTemps',
        ];
    }

    public function rappelSaisieTemps(RappelSaisieTempsNotification $event): void
    {
        $societe = $event->getSociete();

        if (!$societe->getEnabled()){
            return;
        }

        $month = $this->dateMonthService->normalize($event->getMonth());
        $societeUsers = $this->societeUserRepository->findAllNotifiableUsers('notificationSaisieTempsEnabled', $societe);

        foreach ($societeUsers as $societeUser) {
            $this->sendNotificationSaisieTempsEmail($societeUser, $month);
        }
    }

    private function sendNotificationSaisieTempsEmail(SocieteUser $societeUser, DateTimeInterface $month): void
    {
        if (null === $societeUser->getUser()->getEmail()) {
            return;
        }

        $cra = $societeUser
            ->getCras()
            ->filter(function (Cra $cra) use ($month) {
                return $this->dateMonthService->isSameMonth($cra->getMois(), $month);
            })
            ->first()
        ;

        $email = (new TemplatedEmail())
            ->to($societeUser->getUser()->getEmail())
            ->subject('Saisie de vos temps sur RDI-Manager')
            ->textTemplate('corp_app/mail/notification_saisie_temps.txt.twig')
            ->htmlTemplate('corp_app/mail/notification_saisie_temps.html.twig')
            ->context([
                'month' => $month,
                'cra' => $cra,
                'societe' => $societeUser->getSociete(),
            ])
        ;

        $this->mailer->send($email);
    }
}

<?php

namespace App\Notification\Mail;

use App\Entity\Cra;
use App\Entity\User;
use App\Notification\Event\RappelSaisieTempsNotification;
use App\Repository\UserRepository;
use App\Service\DateMonthService;
use DateTimeInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;

class RappelSaisieTemps implements EventSubscriberInterface
{
    private UserRepository $userRepository;

    private DateMonthService $dateMonthService;

    private MailerInterface $mailer;

    public function __construct(
        UserRepository $userRepository,
        DateMonthService $dateMonthService,
        MailerInterface $mailer
    ) {
        $this->userRepository = $userRepository;
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
        $month = $this->dateMonthService->normalize($event->getMonth());
        $users = $this->userRepository->findAllNotifiableUsers($societe, 'notificationSaisieTempsEnabled');

        foreach ($users as $user) {
            $this->sendNotificationSaisieTempsEmail($user, $month);
        }
    }

    private function sendNotificationSaisieTempsEmail(User $user, DateTimeInterface $month): void
    {
        $cra = $user
            ->getCras()
            ->filter(function (Cra $cra) use ($month) {
                return $this->dateMonthService->isSameMonth($cra->getMois(), $month);
            })
            ->first()
        ;

        $email = (new TemplatedEmail())
            ->to($user->getEmail())
            ->subject('Saisie de vos temps sur RDI-Manager')
            ->textTemplate('mail/notification_saisie_temps.txt.twig')
            ->htmlTemplate('mail/notification_saisie_temps.html.twig')
            ->context([
                'month' => $month,
                'cra' => $cra,
            ])
        ;

        $this->mailer->send($email);
    }
}

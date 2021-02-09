<?php

namespace App\Mailer;

use App\Entity\Cra;
use App\Entity\User;
use App\Notification\RappelSaisieTempsNotification;
use App\Repository\UserRepository;
use App\Service\DateMonthService;
use DateTimeInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class NotificationListener implements EventSubscriberInterface
{
    private UserRepository $userRepository;

    private UrlGeneratorInterface $urlGenerator;

    private DateMonthService $dateMonthService;

    private MailerInterface $mailer;

    public function __construct(
        UserRepository $userRepository,
        UrlGeneratorInterface $urlGenerator,
        DateMonthService $dateMonthService,
        MailerInterface $mailer
    ) {
        $this->userRepository = $userRepository;
        $this->urlGenerator = $urlGenerator;
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
        $link = $this->urlGenerator->generate('app_fo_temps', [
            'year' => $month->format('Y'),
            'month' => $month->format('m'),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

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
                'link' => $link,
                'month' => $month,
                'cra' => $cra,
            ])
        ;

        $this->mailer->send($email);
    }
}

<?php

namespace App\Notification\Sms;

use App\Entity\Cra;
use App\Entity\Societe;
use App\Entity\User;
use App\Notification\Event\RappelSaisieTempsNotification;
use App\Repository\UserRepository;
use App\Service\DateMonthService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Twig\Environment as TwigEnvironment;

class RappelSaisieTemps implements EventSubscriberInterface
{
    private UserRepository $userRepository;

    private DateMonthService $dateMonthService;

    private TwigEnvironment $twig;

    private SmsSender $smsSender;

    public function __construct(
        UserRepository $userRepository,
        DateMonthService $dateMonthService,
        TwigEnvironment $twig,
        SmsSender $smsSender
    ) {
        $this->userRepository = $userRepository;
        $this->dateMonthService = $dateMonthService;
        $this->twig = $twig;
        $this->smsSender = $smsSender;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RappelSaisieTempsNotification::class => 'sendNotificationSaisieTempsAllUsers',
        ];
    }

    /**
     * @param User $user Utilisateur à rappeller
     *
     * @return bool Si l'utilisateur va recevoir un sms
     */
    public function sendNotificationSaisieTempsSms(User $user, \DateTimeInterface $month = null): void
    {
        $month = $this->dateMonthService->normalize($month ?? new \DateTime());

        $cra = $user
            ->getCras()
            ->filter(function (Cra $cra) use ($month) {
                return $this->dateMonthService->isSameMonth($cra->getMois(), $month);
            })
            ->first()
        ;

        $message = $this->twig->render('mail/notification_saisie_temps.txt.twig', [
            'month' => $month,
            'cra' => $cra,
        ]);

        $this->smsSender->sendSms($user, $message);
    }

    public function sendNotificationSaisieTempsAllUsers(RappelSaisieTempsNotification $event): void
    {
        $societe = $event->getSociete();
        $month = $event->getMonth();
        $users = $this->userRepository->findAllNotifiableUsers($societe, 'notificationSaisieTempsEnabled');

        foreach ($users as $user) {
            if ($societe->getSmsEnabled() && null !== $user->getTelephone()) {
                $this->sendNotificationSaisieTempsSms($user, $month);
            }
        }
    }
}

<?php

namespace App\Service;

use App\Entity\Cra;
use App\Entity\Societe;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment as TwigEnvironment;

/**
 * Service pour envoyer les notifications pour rappeller de remplir ses temps.
 */
class NotificationSaisieTemps
{
    private UserRepository $userRepository;

    private UrlGeneratorInterface $urlGenerator;

    private DateMonthService $dateMonthService;

    private TwigEnvironment $twig;

    private SmsSender $smsSender;

    public function __construct(
        UserRepository $userRepository,
        UrlGeneratorInterface $urlGenerator,
        DateMonthService $dateMonthService,
        TwigEnvironment $twig,
        SmsSender $smsSender
    ) {
        $this->userRepository = $userRepository;
        $this->urlGenerator = $urlGenerator;
        $this->dateMonthService = $dateMonthService;
        $this->twig = $twig;
        $this->smsSender = $smsSender;
    }

    /**
     * @param User $user Utilisateur à rappeller
     *
     * @return bool Si l'utilisateur va recevoir un sms
     */
    public function sendNotificationSaisieTempsSms(User $user, \DateTimeInterface $month = null): void
    {
        $month = $this->dateMonthService->normalize($month ?? new \DateTime());

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

        $message = $this->twig->render('mail/notification_saisie_temps.txt.twig', [
            'link' => $link,
            'month' => $month,
            'cra' => $cra,
        ]);

        $this->smsSender->sendSms($user, $message);
    }

    public function sendNotificationSaisieTempsAllUsers(Societe $societe, \DateTimeInterface $month = null): void
    {
        $users = $this->userRepository->findAllNotifiableUsers($societe, 'notificationSaisieTempsEnabled');

        foreach ($users as $user) {
            if ($societe->getSmsEnabled() && null !== $user->getTelephone()) {
                $this->sendNotificationSaisieTempsSms($user, $month);
            }
        }
    }
}

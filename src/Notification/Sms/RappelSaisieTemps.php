<?php

namespace App\Notification\Sms;

use App\Entity\Cra;
use App\Entity\SocieteUser;
use App\Notification\Event\RappelSaisieTempsNotification;
use App\Repository\SocieteUserRepository;
use App\Service\DateMonthService;
use App\SocieteProduct\Product\ProductPrivileges;
use App\SocieteProduct\ProductPrivilegeCheker;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Twig\Environment as TwigEnvironment;

class RappelSaisieTemps implements EventSubscriberInterface
{
    private SocieteUserRepository $societeUserRepository;

    private DateMonthService $dateMonthService;

    private TwigEnvironment $twig;

    private SmsSender $smsSender;

    public function __construct(
        SocieteUserRepository $societeUserRepository,
        DateMonthService $dateMonthService,
        TwigEnvironment $twig,
        SmsSender $smsSender
    ) {
        $this->societeUserRepository = $societeUserRepository;
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
     * @param SocieteUser $societeUser Utilisateur à rappeller
     *
     * @return bool Si l'utilisateur va recevoir un sms
     */
    public function sendNotificationSaisieTempsSms(SocieteUser $societeUser, \DateTimeInterface $month = null): void
    {
        $month = $this->dateMonthService->normalize($month ?? new \DateTime());

        $cra = $societeUser
            ->getCras()
            ->filter(function (Cra $cra) use ($month) {
                return $this->dateMonthService->isSameMonth($cra->getMois(), $month);
            })
            ->first()
        ;

        $message = $this->twig->render('corp_app/sms/notification_saisie_temps.txt.twig', [
            'month' => $month,
            'cra' => $cra,
        ]);

        $this->smsSender->sendSms($societeUser->getUser()->getTelephone(), $message);
    }

    public function sendNotificationSaisieTempsAllUsers(RappelSaisieTempsNotification $event): void
    {
        $societe = $event->getSociete();

        if (!$societe->getEnabled()){
            return;
        }

        if (ProductPrivilegeCheker::checkProductPrivilege($societe,ProductPrivileges::SMS_NOTIFICATION_SAISIE_TEMPS)){
            $month = $event->getMonth();
            $societeUsers = $this->societeUserRepository->findAllNotifiableUsers('notificationSaisieTempsEnabled', $societe);

            foreach ($societeUsers as $societeUser) {
                if ($societe->getSmsEnabled() && null !== $societeUser->getUser()->getTelephone()) {
                    $this->sendNotificationSaisieTempsSms($societeUser, $month);
                }
            }
        }
    }
}

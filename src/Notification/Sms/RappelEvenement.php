<?php

namespace App\Notification\Sms;

use App\Entity\EvenementParticipant;
use App\Notification\Event\RemindeEvenementEvent;
use App\Repository\SocieteUserRepository;
use App\Service\DateMonthService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Twig\Environment as TwigEnvironment;

class RappelEvenement implements EventSubscriberInterface
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
            RemindeEvenementEvent::class => 'sendNotificationRappelEvenement',
        ];
    }

    public function sendNotificationRappelEvenementSms(EvenementParticipant $evenementParticipant): void
    {
        $message = $this->twig->render('corp_app/sms/reminde_evenement.txt.twig', [
            'evenement' => $evenementParticipant->getEvenement(),
        ]);

        $this->smsSender->sendSms($evenementParticipant->getSocieteUser()->getUser()->getTelephone(), $message);
    }

    public function sendNotificationRappelEvenement(RemindeEvenementEvent $event): void
    {
        $evenement = $event->getEvenement();
        $societe = $event->getSociete();

        if (!$societe->getEnabled()){
            return;
        }

        foreach ($evenement->getRequiredEvenementParticipants() as $requiredEvenementParticipant) {
            if ($societe->getSmsEnabled() && null !== $requiredEvenementParticipant->getSocieteUser()->getUser()->getTelephone()) {
                $this->sendNotificationRappelEvenementSms($requiredEvenementParticipant);
            }
        }
    }
}

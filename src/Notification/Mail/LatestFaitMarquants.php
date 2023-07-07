<?php

namespace App\Notification\Mail;

use App\Entity\SocieteUser;
use App\Notification\Event\LatestFaitMarquantsNotification;
use App\Repository\FaitMarquantRepository;
use App\Repository\SocieteUserRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class LatestFaitMarquants implements EventSubscriberInterface
{
    private SocieteUserRepository $societeUserRepository;

    private FaitMarquantRepository $faitMarquantRepository;

    private TranslatorInterface $translator;

    private MailerInterface $mailer;

    public function __construct(
        SocieteUserRepository $societeUserRepository,
        FaitMarquantRepository $faitMarquantRepository,
        TranslatorInterface $translator,
        MailerInterface $mailer
    ) {
        $this->societeUserRepository = $societeUserRepository;
        $this->faitMarquantRepository = $faitMarquantRepository;
        $this->translator = $translator;
        $this->mailer = $mailer;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LatestFaitMarquantsNotification::class => 'sendLatestFaitsMarquantsToAllUsers',
        ];
    }

    public function sendLatestFaitsMarquants(SocieteUser $societeUser): void
    {
        if (!$societeUser->getSociete()->getEnabled()){
            return;
        }

        if (null === $societeUser->getUser()->getEmail()) {
            return;
        }

        $from = (new \DateTime())->modify('-7days');
        $faitMarquants = $this->faitMarquantRepository->findLatestOnUserProjets($societeUser, $from);

        if (0 === count($faitMarquants)) {
            return;
        }

        $title = $this->translator->trans('n_nouveaux_faits_marquants', ['n' => count($faitMarquants)]);
        $title .= ' ajoutés à vos projets RDI-Manager';

        $email = (new TemplatedEmail())
            ->to($societeUser->getUser()->getEmail())
            ->subject($title)
            ->textTemplate('corp_app/mail/notification_latest_faits_marquants.txt.twig')
            ->htmlTemplate('corp_app/mail/notification_latest_faits_marquants.html.twig')
            ->context([
                'faitMarquants' => $faitMarquants,
                'societe' => $societeUser->getSociete(),
            ])
        ;

        $this->mailer->send($email);
    }

    public function sendLatestFaitsMarquantsToAllUsers(LatestFaitMarquantsNotification $event): void
    {
        $societeUsers = $this->societeUserRepository->findAllNotifiableUsers(
            'notificationLatestFaitMarquantEnabled',
            $event->getSociete()
        );

        foreach ($societeUsers as $societeUser) {
            $this->sendLatestFaitsMarquants($societeUser);
        }
    }
}

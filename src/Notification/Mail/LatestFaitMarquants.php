<?php

namespace App\Notification\Mail;

use App\Entity\User;
use App\Notification\Event\LatestFaitMarquantsNotification;
use App\Repository\FaitMarquantRepository;
use App\Repository\UserRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class LatestFaitMarquants implements EventSubscriberInterface
{
    private UserRepository $userRepository;

    private FaitMarquantRepository $faitMarquantRepository;

    private TranslatorInterface $translator;

    private MailerInterface $mailer;

    public function __construct(
        UserRepository $userRepository,
        FaitMarquantRepository $faitMarquantRepository,
        TranslatorInterface $translator,
        MailerInterface $mailer
    ) {
        $this->userRepository = $userRepository;
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

    public function sendLatestFaitsMarquants(User $user): void
    {
        $from = (new \DateTime())->modify('-7days');
        $faitMarquants = $this->faitMarquantRepository->findLatestOnUserProjets($user, $from);

        if (0 === count($faitMarquants)) {
            return;
        }

        $title = $this->translator->trans('n_nouveaux_faits_marquants', ['n' => count($faitMarquants)]);
        $title .= ' ajoutés à vos projets RDI-Manager';

        $email = (new TemplatedEmail())
            ->to($user->getEmail())
            ->subject($title)
            ->textTemplate('mail/notification_latest_faits_marquants.txt.twig')
            ->htmlTemplate('mail/notification_latest_faits_marquants.html.twig')
            ->context([
                'faitMarquants' => $faitMarquants,
            ])
        ;

        $this->mailer->send($email);
    }

    public function sendLatestFaitsMarquantsToAllUsers(LatestFaitMarquantsNotification $event): void
    {
        $users = $this->userRepository->findAllNotifiableUsers(
            $event->getSociete(),
            'notificationLatestFaitMarquantEnabled'
        );

        foreach ($users as $user) {
            $this->sendLatestFaitsMarquants($user);
        }
    }
}

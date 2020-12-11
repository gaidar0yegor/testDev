<?php

namespace App\Service;

use App\Entity\Cra;
use App\Entity\Societe;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Service pour envoyer les notifications pour rappeller de remplir ses temps.
 */
class NotificationSaisieTemps
{
    private UserRepository $userRepository;

    private UrlGeneratorInterface $urlGenerator;

    private DateMonthService $dateMonthService;

    private RdiMailer $rdiMailer;

    private MailerInterface $mailer;

    public function __construct(
        UserRepository $userRepository,
        UrlGeneratorInterface $urlGenerator,
        DateMonthService $dateMonthService,
        RdiMailer $rdiMailer,
        MailerInterface $mailer
    ) {
        $this->userRepository = $userRepository;
        $this->urlGenerator = $urlGenerator;
        $this->dateMonthService = $dateMonthService;
        $this->rdiMailer = $rdiMailer;
        $this->mailer = $mailer;
    }

    /**
     * @param User $user Utilisateur à rappeller
     *
     * @return bool Si l'utilisateur va recevoir un mail
     */
    public function sendNotificationSaisieTemps(User $user, \DateTimeInterface $month = null): bool
    {
        $month = $this->dateMonthService->normalize($month ?? new \DateTime());

        $link = $this->urlGenerator->generate('temps_', [
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

        $email = $this->rdiMailer
            ->createDefaultEmail()
            ->to($user->getEmail())
            ->subject('Saisie de vos temps sur RDI Manager')
            ->text(sprintf(
                'Saisie de vos temps sur RDI Manager.'
                .' Saisissez vos temps sur : %s',
                $link
            ))

            ->htmlTemplate('mail/notification_saisie_temps.html.twig')
            ->context([
                'link' => $link,
                'month' => $month,
                'cra' => $cra,
            ])
        ;

        $this->mailer->send($email);

        return true;
    }

    public function sendNotificationSaisieTempsAllUsers(Societe $societe, \DateTimeInterface $month = null): int
    {
        $users = $this->userRepository->findAllNotifiableUsers($societe, 'notificationSaisieTempsEnabled');
        $totalMailSent = 0;

        foreach ($users as $user) {
            $mailSent = $this->sendNotificationSaisieTemps($user, $month);

            if ($mailSent) {
                ++$totalMailSent;
            }
        }

        return $totalMailSent;
    }
}

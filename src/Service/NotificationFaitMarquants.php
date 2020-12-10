<?php

namespace App\Service;

use App\Entity\FaitMarquant;
use App\Entity\Projet;
use App\Entity\Societe;
use App\Entity\User;
use App\Repository\FaitMarquantRepository;
use App\Repository\ProjetRepository;
use App\Repository\UserRepository;
use App\Role;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Service pour envoyer les notifications relatives aux faits marquants.
 */
class NotificationFaitMarquants
{
    private UserRepository $userRepository;

    private ProjetRepository $projetRepository;

    private FaitMarquantRepository $faitMarquantRepository;

    private UrlGeneratorInterface $urlGenerator;

    private RdiMailer $rdiMailer;

    private TranslatorInterface $translator;

    private MailerInterface $mailer;

    public function __construct(
        UserRepository $userRepository,
        ProjetRepository $projetRepository,
        FaitMarquantRepository $faitMarquantRepository,
        UrlGeneratorInterface $urlGenerator,
        RdiMailer $rdiMailer,
        TranslatorInterface $translator,
        MailerInterface $mailer
    ) {
        $this->userRepository = $userRepository;
        $this->projetRepository = $projetRepository;
        $this->faitMarquantRepository = $faitMarquantRepository;
        $this->urlGenerator = $urlGenerator;
        $this->rdiMailer = $rdiMailer;
        $this->translator = $translator;
        $this->mailer = $mailer;
    }

    /**
     * @param User $user Utilisateur à rappeller de créer ses faits marquants sur les projets dont il contribue.
     *                      Ne fait rien si l'utilisateur ne contribue à aucun projet ce mois ci.
     *
     * @return bool Si l'utilisateur contribue à au moins un projet et va recevoir un email.
     */
    public function sendReminderFaitMarquant(User $user): bool
    {
        $projets = $this->projetRepository->findAllForUser($user, Role::CONTRIBUTEUR, new \DateTime());

        if (0 === count($projets)) {
            return false;
        }

        $email = $this->rdiMailer
            ->createDefaultEmail()
            ->to($user->getEmail())
            ->subject('Rappel pour créer vos faits marquants sur RDI Manager')
            ->text(sprintf(
                'Vous avez contribué à au moins un projet ce mois ci. '.
                'Si vous avez un fait marquant à ajouter, vous êtes invité à le faire ici : %s',
                join(
                    ' ; ',
                    array_map(
                        function (Projet $projet) {
                            $acronyme = $projet->getAcronyme();
                            $link = $this->urlGenerator->generate(
                                'fait_marquant_ajouter',
                                ['projetId' => $projet->getId()],
                                UrlGeneratorInterface::ABSOLUTE_URL
                            );

                            return "Projet $acronyme : $link";
                        },
                        $projets
                    )
                )
            ))

            ->htmlTemplate('mail/notification_create_fait_marquant.html.twig')
            ->context([
                'projets' => $projets,
            ])
        ;

        $this->mailer->send($email);

        return true;
    }

    /**
     * @return int Nombre d'utilisateurs qui vont recevoir un email.
     */
    public function remindCreateAllUsers(Societe $societe): int
    {
        $totalSent = 0;
        $users = $this->userRepository->findAllNotifiableUsers($societe);

        foreach ($users as $user) {
            $sent = $this->sendReminderFaitMarquant($user);

            if ($sent) {
                ++$totalSent;
            }
        }

        return $totalSent;
    }

    public function sendLatestFaitsMarquants(User $user): bool
    {
        $from = (new \DateTime())->modify('-7days');
        $faitMarquants = $this->faitMarquantRepository->findLatestOnUserProjets($user, $from);

        if (0 === count($faitMarquants)) {
            return false;
        }

        $title = $this->translator->trans('n_nouveaux_faits_marquants', ['n' => count($faitMarquants)]);
        $title .= ' ajoutés à vos projets RDI Manager';

        $email = $this->rdiMailer
            ->createDefaultEmail()
            ->to($user->getEmail())
            ->subject($title)
            ->text(sprintf(
                '%s : %s',
                $title,
                join(
                    ', ',
                    array_map(function (FaitMarquant $faitMarquant) {
                        return $faitMarquant->getDate()->format('d/m/Y').' : '.$faitMarquant->getTitre();
                    }, $faitMarquants)
                )
            ))

            ->htmlTemplate('mail/notification_latest_faits_marquants.html.twig')
            ->context([
                'faitMarquants' => $faitMarquants,
            ])
        ;

        $this->mailer->send($email);

        return true;
    }

    public function sendLatestFaitsMarquantsToAllUsers(Societe $societe): int
    {
        $totalSent = 0;
        $users = $this->userRepository->findAllNotifiableUsers($societe);

        foreach ($users as $user) {
            $sent = $this->sendLatestFaitsMarquants($user);

            if ($sent) {
                ++$totalSent;
            }
        }

        return $totalSent;
    }
}

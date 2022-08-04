<?php

namespace App\Onboarding\Listener;

use App\Onboarding\Notification\AddProjects;
use App\Onboarding\Notification\FinalizeInscription;
use App\Onboarding\Notification\FinalSuccess;
use App\Onboarding\Notification\InviteCollaborators;
use App\Security\Role\RoleSociete;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;

/**
 * Ecoute les event d'onboarding, et envoi un mail si l'utilisateur
 * n'a pas recu de mails pendant assez longtemps.
 */
class OnboardingNotificationListener implements EventSubscriberInterface
{
    private MailerInterface $mailer;

    private EntityManagerInterface $em;

    public function __construct(
        MailerInterface $mailer,
        EntityManagerInterface $em
    ) {
        $this->mailer = $mailer;
        $this->em = $em;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FinalizeInscription::class => 'finalizeInscription',
            InviteCollaborators::class => 'inviteCollaborators',
            AddProjects::class => 'addProjects',
            FinalSuccess::class => 'finalSuccess',
        ];
    }

    public function finalizeInscription(FinalizeInscription $onboardingNotification): void
    {
        $societeUser = $onboardingNotification->getSocieteUser();
        $societe = $societeUser->getSociete();

        if (null === $societeUser->getInvitationEmail()) {
            return;
        }

        $email = (new TemplatedEmail())
            ->to($societeUser->getInvitationEmail())
            ->context([
                'societe' => $societe,
                'societeUser' => $societeUser,
            ])
        ;

        switch ($societeUser->getRole()) {
            case RoleSociete::ADMIN:
                $email
                    ->subject(sprintf(
                        'Vous avez été invité sur RDI-Manager en qualité d\'administrateur %s',
                        $societe->getRaisonSociale()
                    ))
                    ->htmlTemplate('corp_app/mail/onboarding/finalize_admin.html.twig')
                    ->textTemplate('corp_app/mail/onboarding/finalize_admin.txt.twig')
                ;
                break;

            case RoleSociete::CDP:
                $email
                    ->subject(sprintf(
                        'Vous avez été invité sur RDI-Manager en qualité de chef de projet %s',
                        $societe->getRaisonSociale()
                    ))
                    ->htmlTemplate('corp_app/mail/onboarding/finalize_cdp.html.twig')
                    ->textTemplate('corp_app/mail/onboarding/finalize_cdp.txt.twig')
                ;
                break;

            case RoleSociete::USER:
                $email
                    ->subject(sprintf(
                        'Vous avez été invité sur RDI-Manager pour suivre les projets de %s',
                        $societe->getRaisonSociale()
                    ))
                    ->htmlTemplate('corp_app/mail/onboarding/finalize_user.html.twig')
                    ->textTemplate('corp_app/mail/onboarding/finalize_user.txt.twig')
                ;
                break;
        }

        $this->mailer->send($email);

        $societeUser->setNotificationOnboardingLastSentAt(new DateTime());

        $this->em->flush();
    }

    public function inviteCollaborators(InviteCollaborators $onboardingNotification): void
    {
        $societeUser = $onboardingNotification->getSocieteUser();
        $societe = $societeUser->getSociete();

        if (null === $societeUser->getUser()->getEmail()) {
            return;
        }

        $email = (new TemplatedEmail())
            ->to($societeUser->getUser()->getEmail())
            ->context([
                'societe' => $societe,
                'societeUser' => $societeUser,
            ])
            ->subject('Invitez vos chefs de projets et collaborateurs')
            ->htmlTemplate('corp_app/mail/onboarding/invite_collaborators.html.twig')
            ->textTemplate('corp_app/mail/onboarding/invite_collaborators.txt.twig')
        ;

        $this->mailer->send($email);

        $societeUser->setNotificationOnboardingLastSentAt(new DateTime());

        $this->em->flush();
    }

    public function addProjects(AddProjects $onboardingNotification): void
    {
        $societeUser = $onboardingNotification->getSocieteUser();
        $societe = $societeUser->getSociete();

        if (null === $societeUser->getUser()->getEmail()) {
            return;
        }

        $email = (new TemplatedEmail())
            ->to($societeUser->getUser()->getEmail())
            ->context([
                'societe' => $societe,
                'societeUser' => $societeUser,
            ])
            ->subject('Créer vos projets et répartissez les rôles')
            ->htmlTemplate('corp_app/mail/onboarding/add_projects.html.twig')
            ->textTemplate('corp_app/mail/onboarding/add_projects.txt.twig')
        ;

        $this->mailer->send($email);

        $societeUser->setNotificationOnboardingLastSentAt(new DateTime());

        $this->em->flush();
    }

    public function finalSuccess(FinalSuccess $onboardingNotification): void
    {
        $societeUser = $onboardingNotification->getSocieteUser();
        $societe = $societeUser->getSociete();

        if (null === $societeUser->getUser()->getEmail()) {
            return;
        }

        $template = null;

        switch ($societeUser->getRole()) {
            case RoleSociete::ADMIN:
                $template = 'final_success_admin';
                break;

            case RoleSociete::CDP:
                $template = 'final_success_cdp';
                break;

            case RoleSociete::USER:
                $template = 'final_success_user';
                break;

            default:
                return;
        }

        $email = (new TemplatedEmail())
            ->to($societeUser->getUser()->getEmail())
            ->context([
                'societe' => $societe,
                'societeUser' => $societeUser,
            ])
            ->subject('Bravo ! L\'aventure RDI-Manager est en marche pour vous')
            ->htmlTemplate('corp_app/mail/onboarding/' . $template . '.html.twig')
        ;

        $this->mailer->send($email);

        $societeUser
            ->setNotificationOnboardingLastSentAt(new DateTime())
            ->setNotificationOnboardingFinished(true)
        ;
    }
}

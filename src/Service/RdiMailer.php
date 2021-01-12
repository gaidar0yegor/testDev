<?php

namespace App\Service;

use App\DTO\RecommandationMessage;
use App\Entity\User;
use App\Exception\RdiException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class RdiMailer
{
    private $mailFrom;

    private $mailer;

    private $translator;

    private $urlGenerator;

    public function __construct(
        string $mailFrom,
        MailerInterface $mailer,
        TranslatorInterface $translator,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->mailFrom = $mailFrom;
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->urlGenerator = $urlGenerator;
    }

    public function createDefaultEmail(): TemplatedEmail
    {
        $email = new TemplatedEmail();

        $email
            ->from($this->mailFrom)
        ;

        return $email;
    }

    public function sendTestEmail(string $email): void
    {
        $homeUrl = $this->urlGenerator->generate('app_home', [], UrlGeneratorInterface::ABSOLUTE_URL);

        $email = $this->createDefaultEmail()
            ->to($email)
            ->subject('Email de test envoyé depuis RDI-Manager')
            ->text(sprintf(
                'Ceci est un email de test envoyé depuis RDI-Manager. '
                .'Si vous le recevez, le serveur est bien configuré pour envoyer les emails RDI-Manager. '
                .'Url absolue de RDI-Manager : %s',
                $homeUrl
            ))

            ->htmlTemplate('mail/test_mail.html.twig')
        ;

        $this->mailer->send($email);
    }

    public function sendRecommandationEmail(RecommandationMessage $recommandationMessage): void
    {
        $email = $this->createDefaultEmail()
            ->from($recommandationMessage->getFrom())
            ->to($recommandationMessage->getTo())
            ->addBcc($this->mailFrom)
            ->subject($recommandationMessage->getSubject())
            ->context([
                'customText' => $recommandationMessage->getCustomText(),
            ])
            ->textTemplate('mail/_recommandation-content.txt.twig')

            ->htmlTemplate('mail/recommandation.html.twig')
        ;

        $this->mailer->send($email);
    }

    /**
     * @param User $invitedUser User à inviter, doit avoir un token d'invitation
     * @param User $adminUser Référent qui invite l'user, utile pour afficher "XX vous invite..." dans l'email
     */
    public function sendInvitationEmail(User $invitedUser, User $fromUser): void
    {
        if (null === $invitedUser->getInvitationToken()) {
            throw new RdiException('Cannot send invitation email, this user has no invitation token.');
        }

        $invitationLink = $this->urlGenerator->generate('app_fo_user_finalize_inscription', [
            'token' => $invitedUser->getInvitationToken(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $email = $this->createDefaultEmail()
            ->to($invitedUser->getEmail())
            ->subject(sprintf('%s vous invite sur RDI-Manager', $fromUser->getFullname()))
            ->text(sprintf(
                '%s vous invite sur RDI-Manager dans la société %s en tant que %s.'
                .' Finalisez votre inscription en suivant ce lien : %s',
                $fromUser->getFullname(),
                $invitedUser->getSociete()->getRaisonSociale(),
                $this->translator->trans($invitedUser->getRole()),
                $invitationLink
            ))

            ->htmlTemplate('mail/invite.html.twig')
            ->context([
                'invitedUser' => $invitedUser,
                'fromUser' => $fromUser,
                'invitationLink' => $invitationLink,
            ])
        ;

        $this->mailer->send($email);
    }

    public function sendResetPasswordEmail(User $user): void
    {
        if (!$user->hasResetPasswordToken()) {
            throw new RdiException('Cannot send reset password email, this user has no reset password token.');
        }

        $resetPasswordLink = $this->urlGenerator->generate('app_fo_reset_password', [
            'token' => $user->getResetPasswordToken(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $email = $this->createDefaultEmail()
            ->to($user->getEmail())
            ->subject(sprintf('Réinitialisation de votre mot de passe RDI-Manager'))
            ->text(sprintf(
                'Vous avez demandé un lien de réinitialisation de votre mot de passe. '
                .'Suivez ce lien pour définir votre nouveau mot de passe : %s',
                $resetPasswordLink
            ))

            ->htmlTemplate('mail/reset_password.html.twig')
            ->context([
                'resetPasswordLink' => $resetPasswordLink,
            ])
        ;

        $this->mailer->send($email);
    }
}

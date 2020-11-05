<?php

namespace App\Service;

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

    private function createDefaultEmail(): TemplatedEmail
    {
        $email = new TemplatedEmail();

        $email
            ->from($this->mailFrom)
        ;

        return $email;
    }

    /**
     * @param User $invitedUser User à inviter, doit avoir un token d'invitation
     * @param User $adminUser Référent qui invite l'user, utile pour afficher "XX vous invite..." dans l'email
     */
    public function sendInvitationEmail(User $invitedUser, User $adminUser): void
    {
        if (null === $invitedUser->getInvitationToken()) {
            throw new RdiException('Cannot send invitation email, this user has no invitation token.');
        }

        $invitationLink = $this->urlGenerator->generate('fo_user_finalize_inscription', [
            'token' => $invitedUser->getInvitationToken(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $email = $this->createDefaultEmail()
            ->to($invitedUser->getEmail())
            ->subject(sprintf('%s vous invite sur RDI manager', $adminUser->getFullname()))
            ->text(sprintf(
                '%s vous invite sur RDI manager dans la société %s en tant que %s.'
                .' Finalisez votre inscription en suivant ce lien : %s',
                $adminUser->getFullname(),
                $adminUser->getSociete()->getRaisonSociale(),
                $this->translator->trans($invitedUser->getRole()),
                $invitationLink
            ))

            ->htmlTemplate('mail/invite.html.twig')
            ->context([
                'invitedUser' => $invitedUser,
                'adminUser' => $adminUser,
                'invitationLink' => $invitationLink,
            ])
        ;

        $this->mailer->send($email);
    }
}

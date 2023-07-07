<?php

namespace App\Service;

use App\DTO\RecommandationMessage;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RdiMailer
{
    private $mailFrom;

    private $mailer;

    private $urlGenerator;

    public function __construct(
        string $mailFrom,
        MailerInterface $mailer,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->mailFrom = $mailFrom;
        $this->mailer = $mailer;
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

            ->htmlTemplate('corp_app/mail/test_mail.html.twig')
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
            ->textTemplate('corp_app/mail/_recommandation-content.txt.twig')

            ->htmlTemplate('corp_app/mail/recommandation.html.twig')
        ;

        $this->mailer->send($email);
    }
}

<?php

namespace App\Notification\Mail;

use App\Entity\FaitMarquant;
use App\Entity\ProjetParticipant;
use App\MultiSociete\UserContext;
use App\Service\FaitMarquantService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class FaitMarquantCreated
{
    private MailerInterface $mailer;

    private UserContext $userContext;

    private FaitMarquantService $faitMarquantService;

    public function __construct(
        MailerInterface $mailer,
        UserContext $userContext,
        FaitMarquantService $faitMarquantService
    )
    {
        $this->mailer = $mailer;
        $this->userContext = $userContext;
        $this->faitMarquantService = $faitMarquantService;
    }

    public function postPersist(FaitMarquant $faitMarquant, LifecycleEventArgs $args): void
    {
        $email = (new TemplatedEmail())
            ->subject('Fait marquant ajouté sur le projet '.$faitMarquant->getProjet()->getAcronyme())
            ->htmlTemplate('corp_app/mail/fait_marquant_cree.html.twig')
            ->textTemplate('corp_app/mail/fait_marquant_cree.txt.twig')
            ->context([
                'faitMarquant' => $faitMarquant,
                'societe' => $faitMarquant->getSociete(),
            ])
        ;

        $excluEmails = new ArrayCollection();

        $faitMarquant
            ->getProjet()
            ->getProjetParticipants()
            ->map(function (ProjetParticipant $projetParticipant) use ($email, $excluEmails) {
                if (!$projetParticipant->getWatching()) {
                    return;
                }

                if (!$projetParticipant->getSocieteUser()->hasUser()) {
                    return;
                }

                if (null === $projetParticipant->getSocieteUser()->getUser()->getEmail()) {
                    return;
                }

                if (
                    $this->userContext->hasSocieteUser()
                    && $this->userContext->getSocieteUser() === $projetParticipant->getSocieteUser()
                ) {
                    return;
                }

                $this->mailer->send($email->to($projetParticipant->getSocieteUser()->getUser()->getEmail()));
                $excluEmails->add($projetParticipant->getSocieteUser()->getUser()->getEmail());
            });

        $this->sendToTaggedUsers($faitMarquant, $excluEmails);
    }

    private function sendToTaggedUsers(FaitMarquant $faitMarquant, ArrayCollection $excluEmails)
    {
        $templatedEmail = (new TemplatedEmail())
            ->subject("{$faitMarquant->getCreatedBy()->getUser()->getShortname()} vous a mentionné dans un fait marquant")
            ->htmlTemplate('corp_app/mail/fait_marquant_tagged_user.html.twig')
            ->textTemplate('corp_app/mail/fait_marquant_tagged_user.txt.twig');

        $context = [
            'faitMarquant' => $faitMarquant,
            'societe' => $faitMarquant->getSociete(),
        ];



        foreach ($faitMarquant->getSendedToEmails() as $email){
            if ($excluEmails->contains($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)){
                continue;
            }

            $result = $this->faitMarquantService->inviteUserTaggedSurFm($faitMarquant->getProjet(),$email);
            $templatedEmail->context(array_merge($context,$result));
            $this->mailer->send($templatedEmail->to($email));
        }
    }
}

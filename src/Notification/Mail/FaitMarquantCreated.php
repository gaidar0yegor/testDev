<?php

namespace App\Notification\Mail;

use App\Entity\FaitMarquant;
use App\Entity\ProjetParticipant;
use App\Entity\SocieteUser;
use App\MultiSociete\UserContext;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class FaitMarquantCreated
{
    private MailerInterface $mailer;

    private UserContext $userContext;

    public function __construct(MailerInterface $mailer, UserContext $userContext)
    {
        $this->mailer = $mailer;
        $this->userContext = $userContext;
    }

    public function postPersist(FaitMarquant $faitMarquant, LifecycleEventArgs $args): void
    {
        $email = (new TemplatedEmail())
            ->subject('Fait marquant ajouté sur le projet '.$faitMarquant->getProjet()->getAcronyme())
            ->htmlTemplate('mail/fait_marquant_cree.html.twig')
            ->textTemplate('mail/fait_marquant_cree.txt.twig')
            ->context([
                'faitMarquant' => $faitMarquant,
                'societe' => $faitMarquant->getSociete(),
            ])
        ;

        $sendedTo = new ArrayCollection();

        $faitMarquant
            ->getProjet()
            ->getProjetParticipants()
            ->map(function (ProjetParticipant $projetParticipant) use ($email, $sendedTo) {
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
                $sendedTo->add($projetParticipant->getSocieteUser());
            });

        $emailToMentions = (new TemplatedEmail())
            ->subject("{$faitMarquant->getCreatedBy()->getUser()->getShortname()} vous a envoyé un fait marquant ajouté sur le projet {$faitMarquant->getProjet()->getAcronyme()}")
            ->htmlTemplate('mail/fait_marquant_envoye.html.twig')
            ->textTemplate('mail/fait_marquant_envoye.txt.twig')
        ;
        $context = [
            'faitMarquant' => $faitMarquant,
            'societe' => $faitMarquant->getSociete()
        ];

        $faitMarquant->getSendedToSocieteUsers()->map(function (SocieteUser $societeUser) use ($sendedTo, $emailToMentions, $context) {
            if (!$sendedTo->contains($societeUser)) {
                $context['receiver'] = $societeUser;
                $emailToMentions->context($context);
                $this->mailer->send($emailToMentions->to(
                    $societeUser->hasUser() ? $societeUser->getUser()->getEmail() : $societeUser->getInvitationEmail()
                ));
            }
        })
        ;
    }
}

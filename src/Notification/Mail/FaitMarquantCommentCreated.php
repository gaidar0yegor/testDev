<?php

namespace App\Notification\Mail;

use App\Entity\FaitMarquantComment;
use App\MultiSociete\UserContext;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class FaitMarquantCommentCreated
{
    private MailerInterface $mailer;

    private UserContext $userContext;

    public function __construct(
        MailerInterface $mailer,
        UserContext $userContext
    )
    {
        $this->mailer = $mailer;
        $this->userContext = $userContext;
    }

    public function postPersist(FaitMarquantComment $faitMarquantComment, LifecycleEventArgs $args): void
    {
        $user = $faitMarquantComment->getCreatedBy()->getUser();
        $faitMarquant = $faitMarquantComment->getFaitMarquant();

        $email = (new TemplatedEmail())
            ->subject($user->getFullname() . ' a commenté un fait marquant sur le projet '. $faitMarquant->getProjet()->getAcronyme())
            ->htmlTemplate('corp_app/mail/fait_marquant_comment_cree.html.twig')
            ->textTemplate('corp_app/mail/fait_marquant_comment_cree.txt.twig')
            ->context([
                'faitMarquantComment' => $faitMarquantComment,
                'faitMarquant' => $faitMarquant,
                'user' => $user,
                'societe' => $faitMarquant->getSociete(),
            ]);

        if ($faitMarquant->getProjet()->getChefDeProjet()->getUser() !== $faitMarquantComment->getCreatedBy()->getUser()){
            $this->mailer->send($email->to($faitMarquant->getProjet()->getChefDeProjet()->getUser()->getEmail()));
        }
        if ($faitMarquant->getCreatedBy()->getUser() !== $faitMarquantComment->getCreatedBy()->getUser()){
            $this->mailer->send($email->to($faitMarquant->getCreatedBy()->getUser()->getEmail()));
        }
    }
}

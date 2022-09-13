<?php

namespace App\Activity\Type;

use App\Activity\ActivityInterface;
use App\Entity\Activity;
use App\Entity\Projet;
use App\Entity\ProjetActivity;
use App\Entity\Evenement;
use App\Entity\SocieteUser;
use App\Entity\SocieteUserEvenementNotification;
use App\Notification\Mail\EvenementInvitation;
use App\Service\EntityLink\EntityLinkGenerator\EvenementLink;
use App\Service\EntityLink\EntityLinkService;
use App\MultiSociete\UserContext;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvenementRemovedActivity implements ActivityInterface
{
    private EntityManagerInterface $em;

    private EntityLinkService $entityLinkService;

    private UserContext $userContext;

    private EvenementInvitation $mailEvenementInvitation;

    public function __construct(
        EntityLinkService $entityLinkService,
        UserContext $userContext,
        EntityManagerInterface $em,
        EvenementInvitation $mailEvenementInvitation
    )
    {
        $this->em = $em;
        $this->entityLinkService = $entityLinkService;
        $this->userContext = $userContext;
        $this->mailEvenementInvitation = $mailEvenementInvitation;
    }

    public static function getType(): string
    {
        return 'evenement_removed';
    }

    public static function getFilterType(): string
    {
        return 'evenement';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'evenement',
            'projet',
            'removedBy'
        ]);

        $resolver->setAllowedTypes('evenement', 'integer');
        $resolver->setAllowedTypes('projet', ['integer','string']);
        $resolver->setAllowedTypes('removedBy', 'integer');
    }

    public function render(array $activityParameters, Activity $activity): string
    {
        $textProjet = $activityParameters['projet']
            ? ' sur le projet ' . $this->entityLinkService->generateLink(Projet::class, $activityParameters['projet'])
            : '';

        return sprintf(
            '%s %s a annulé l\'évènement %s%s.',
            '<i class="fa fa-calendar" aria-hidden="true"></i>',
            $this->entityLinkService->generateLink(SocieteUser::class, $activityParameters['removedBy']),
            $activityParameters['evenement'],
            $textProjet
        );
    }

    public function preRemove(Evenement $evenement, LifecycleEventArgs $args): void
    {
        $em = $args->getEntityManager();

        $activity = new Activity();
        $activity
            ->setType(self::getType())
            ->setParameters([
                'evenement' => mb_strlen($evenement->getText()) <= EvenementLink::MAX_SIZE ? $evenement->getText() : mb_substr($evenement->getText(), 0, EvenementLink::MAX_SIZE).'…',
                'projet' => $evenement->getProjet() ? intval($evenement->getProjet()->getId()) : '',
                'removedBy' => intval($this->userContext->getSocieteUser()->getId()),
            ])
        ;

        if ($evenement->getProjet()){
            $projetActivity = new ProjetActivity();
            $projetActivity
                ->setProjet($evenement->getProjet())
                ->setActivity($activity)
            ;
            $em->persist($projetActivity);
        }

        foreach ($evenement->getEvenementParticipants() as $evenementParticipant) {
            if ($evenementParticipant->getSocieteUser() !== $this->userContext->getSocieteUser()){
                $em->persist(SocieteUserEvenementNotification::create($activity, $evenementParticipant->getSocieteUser()));
            }
        }

        $em->persist($activity);

        $this->mailEvenementInvitation->sendMailPreRemove($evenement);

        $em->flush();
    }
}

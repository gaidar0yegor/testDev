<?php

namespace App\Activity\Type;

use App\Activity\ActivityInterface;
use App\Entity\Activity;
use App\Entity\Projet;
use App\Entity\ProjetActivity;
use App\Entity\ProjetParticipant;
use App\Entity\SocieteUser;
use App\Entity\SocieteUserActivity;
use App\Entity\SocieteUserNotification;
use App\Service\EntityLink\EntityLinkService;
use App\MultiSociete\UserContext;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjetParticipantRemoved implements ActivityInterface
{
    private EntityLinkService $entityLinkService;

    private UserContext $userContext;

    public function __construct(EntityLinkService $entityLinkService, UserContext $userContext)
    {
        $this->entityLinkService = $entityLinkService;
        $this->userContext = $userContext;
    }
    public static function getType(): string
    {
        return 'projet_participant_removed';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'projet',
            'participant',
            'removedBy',
        ]);

        $resolver->setAllowedTypes('projet', 'integer');
        $resolver->setAllowedTypes('participant', 'integer');
        $resolver->setAllowedTypes('removedBy', 'integer');
    }

    public function render(array $activityParameters, Activity $activity): string
    {
        return sprintf(
            "%s %s n'est plus sur le projet %s, retiré par %s.",
            '<i class="fa fa-trash" aria-hidden="true"></i>',
            $this->entityLinkService->generateLink(SocieteUser::class, $activityParameters['participant']),
            $this->entityLinkService->generateLink(Projet::class, $activityParameters['projet']),
            $this->entityLinkService->generateLink(SocieteUser::class, $activityParameters['removedBy'])
         );
    }

    public function postRemove(ProjetParticipant $projetParticipant, LifecycleEventArgs $args): void
    {
        $projet = $projetParticipant->getProjet();
        $participant = $projetParticipant->getSocieteUser();
        $removedBy = $this->userContext->getSocieteUser();

        $activity = new Activity();
        $activity
            ->setType(self::getType())
            ->setParameters([
                'projet' => intval($projet->getId()),
                'participant' => intval($participant->getId()),
                'removedBy' => intval($removedBy->getId()),
            ])
        ;

        $projetActivity = new ProjetActivity();
        $projetActivity
            ->setActivity($activity)
            ->setProjet($projet)
        ;

        $societeUserNotification = SocieteUserNotification::create($activity,$participant);

        $societeUserActivity = new SocieteUserActivity();
        $societeUserActivity
            ->setSocieteUser($participant)
            ->setActivity($activity)
        ;

        $em = $args->getEntityManager();
        $em->persist($activity);
        $em->persist($projetActivity);
        $em->persist($societeUserActivity);
        $em->persist($societeUserNotification);
        $em->flush();
    }
}

<?php

namespace App\Activity\Type;

use App\Activity\ActivityInterface;
use App\Entity\Activity;
use App\Entity\FaitMarquant;
use App\Entity\Projet;
use App\Entity\ProjetActivity;
use App\Entity\User;
use App\Entity\UserActivity;
use App\Entity\UserNotification;
use App\Role;
use App\Service\EntityLink\EntityLinkService;
use App\Service\ParticipantService;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FaitMarquantCreatedActivity implements ActivityInterface
{
    private EntityLinkService $entityLinkService;

    private ParticipantService $participantService;

    public function __construct(EntityLinkService $entityLinkService, ParticipantService $participantService)
    {
        $this->entityLinkService = $entityLinkService;
        $this->participantService = $participantService;
    }

    public static function getType(): string
    {
        return 'fait_marquant_created';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'projet',
            'createdBy',
            'faitMarquant',
        ]);

        $resolver->setAllowedTypes('projet', 'integer');
        $resolver->setAllowedTypes('createdBy', 'integer');
        $resolver->setAllowedTypes('faitMarquant', 'integer');
    }

    public function render(array $activityParameters, Activity $activity): string
    {
        return sprintf(
            '%s %s a ajouté le fait marquant %s sur le projet %s.',
            '<i class="fa fa-map-marker" aria-hidden="true"></i>',
            $this->entityLinkService->generateLink(User::class, $activityParameters['createdBy']),
            $this->entityLinkService->generateLink(FaitMarquant::class, $activityParameters['faitMarquant']),
            $this->entityLinkService->generateLink(Projet::class, $activityParameters['projet'])
        );
    }

    public function postPersist(FaitMarquant $faitMarquant, LifecycleEventArgs $args): ?Activity
    {
        $activity = new Activity();
        $activity
            ->setType(self::getType())
            ->setParameters([
                'projet' => intval($faitMarquant->getProjet()->getId()),
                'createdBy' => intval($faitMarquant->getCreatedBy()->getId()),
                'faitMarquant' => intval($faitMarquant->getId()),
            ])
        ;

        $userActivity = new UserActivity();
        $userActivity
            ->setUser($faitMarquant->getCreatedBy())
            ->setActivity($activity)
        ;

        $projetActivity = new ProjetActivity();
        $projetActivity
            ->setProjet($faitMarquant->getProjet())
            ->setActivity($activity)
        ;

        $em = $args->getEntityManager();

        $observateurs = $this->participantService->getProjetParticipantsWithRole(
            $faitMarquant->getProjet()->getProjetParticipants(),
            Role::OBSERVATEUR
        );

        foreach ($observateurs as $observateur) {
            if ($observateur->getUser() === $faitMarquant->getCreatedBy()) {
                continue;
            }

            $em->persist(UserNotification::create($activity, $observateur->getUser()));
        }

        $em->persist($activity);
        $em->persist($userActivity);
        $em->persist($projetActivity);
        $em->flush();

        return $activity;
    }
}

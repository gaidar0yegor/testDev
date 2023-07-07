<?php

namespace App\Activity\Type;

use App\Activity\ActivityInterface;
use App\Entity\Activity;
use App\Entity\FaitMarquant;
use App\Entity\Projet;
use App\Entity\ProjetActivity;
use App\Entity\SocieteUser;
use App\Entity\SocieteUserActivity;
use App\Entity\SocieteUserNotification;
use App\Security\Role\RoleProjet;
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

    public static function getFilterType(): string
    {
        return 'fait_marquant';
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
        $isFaitMarquantDeleted = $this->entityLinkService->generateLink(FaitMarquant::class, $activityParameters['faitMarquant'])->getUrl() == "";

        if ($isFaitMarquantDeleted){
            return sprintf(
                '%s %s a <i>supprimé</i> un fait marquant du projet %s.',
                '<i class="fa fa-map-marker" aria-hidden="true"></i>',
                $this->entityLinkService->generateLink(SocieteUser::class, $activityParameters['createdBy']),
                $this->entityLinkService->generateLink(Projet::class, $activityParameters['projet'])
            );
        } else {
            return sprintf(
                '%s %s a ajouté le fait marquant %s sur le projet %s.',
                '<i class="fa fa-map-marker" aria-hidden="true"></i>',
                $this->entityLinkService->generateLink(SocieteUser::class, $activityParameters['createdBy']),
                $this->entityLinkService->generateLink(FaitMarquant::class, $activityParameters['faitMarquant']),
                $this->entityLinkService->generateLink(Projet::class, $activityParameters['projet'])
            );
        }

    }

    public function postPersist(FaitMarquant $faitMarquant, LifecycleEventArgs $args): void
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

        $societeUserActivity = new SocieteUserActivity();
        $societeUserActivity
            ->setSocieteUser($faitMarquant->getCreatedBy())
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
            RoleProjet::OBSERVATEUR
        );

        foreach ($observateurs as $observateur) {
            if ($observateur->getSocieteUser() === $faitMarquant->getCreatedBy()) {
                continue;
            }

            $em->persist(SocieteUserNotification::create($activity, $observateur->getSocieteUser()));
        }

        $em->persist($activity);
        $em->persist($societeUserActivity);
        $em->persist($projetActivity);
        $em->flush();
    }
}

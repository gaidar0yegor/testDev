<?php

namespace App\Activity\Type;

use App\Activity\ActivityInterface;
use App\Entity\Activity;
use App\Entity\Projet;
use App\Entity\ProjetActivity;
use App\Entity\ProjetSuspendPeriod;
use App\Entity\SocieteUser;
use App\Entity\SocieteUserActivity;
use App\Entity\SocieteUserNotification;
use App\Exception\RdiException;
use App\MultiSociete\UserContext;
use App\Service\EntityLink\EntityLinkService;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjetSuspendedActivity implements ActivityInterface
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
        return 'projet_suspended';
    }

    public static function getFilterType(): string
    {
        return 'projet';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'projet',
            'suspendedBy',
        ]);

        $resolver->setAllowedTypes('projet', 'integer');
        $resolver->setAllowedTypes('suspendedBy', 'integer');
    }

    public function render(array $activityParameters, Activity $activity): string
    {
        return sprintf(
            '%s %s a suspendu le projet %s.',
            '<i class="fa fa-times-circle" aria-hidden="true"></i>',
            $this->entityLinkService->generateLink(SocieteUser::class, $activityParameters['suspendedBy']),
            $this->entityLinkService->generateLink(Projet::class, $activityParameters['projet'])
        );
    }

    public function postPersist(ProjetSuspendPeriod $projetSuspendPeriod, LifecycleEventArgs $args): void
    {
        $projet = $projetSuspendPeriod->getProjet();

        $activity = new Activity();
        $activity
            ->setType(self::getType())
            ->setDatetime(new \DateTime($projetSuspendPeriod->getSuspendedAt()->format('Y-m-d'). " " .(new \DateTime())->format('H:i:s')))
            ->setParameters([
                'projet' => intval($projet->getId()),
                'suspendedBy' => intval($this->userContext->getSocieteUser()->getId()),
            ])
        ;

        $societeUserActivity = new SocieteUserActivity();
        $societeUserActivity
            ->setSocieteUser($this->userContext->getSocieteUser())
            ->setActivity($activity)
        ;

        $projetActivity = new ProjetActivity();
        $projetActivity
            ->setProjet($projet)
            ->setActivity($activity)
        ;

        $em = $args->getEntityManager();

        $em->persist($activity);
        $em->persist($societeUserActivity);
        $em->persist($projetActivity);

        foreach ($projet->getProjetParticipants() as $projetParticipant){
            $societeUserNotification = SocieteUserNotification::create($activity,$projetParticipant->getSocieteUser());
            $em->persist($societeUserNotification);
        }

        $em->flush();
    }
}

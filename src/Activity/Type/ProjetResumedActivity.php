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

class ProjetResumedActivity implements ActivityInterface
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
        return 'projet_resumed';
    }

    public static function getFilterType(): string
    {
        return 'projet';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'projet',
            'resumedBy',
        ]);

        $resolver->setAllowedTypes('projet', 'integer');
        $resolver->setAllowedTypes('resumedBy', 'integer');
    }

    public function render(array $activityParameters, Activity $activity): string
    {
        return sprintf(
            '%s %s a ré-activé le projet %s.',
            '<i class="fa fa-check-circle" aria-hidden="true"></i>',
            $this->entityLinkService->generateLink(SocieteUser::class, $activityParameters['resumedBy']),
            $this->entityLinkService->generateLink(Projet::class, $activityParameters['projet'])
        );
    }

    public function postUpdate(ProjetSuspendPeriod $projetSuspendPeriod, LifecycleEventArgs $args): void
    {
        $changes = $args->getEntityManager()->getUnitOfWork()->getEntityChangeSet($projetSuspendPeriod);

        if (isset($changes['resumedAt']) && $changes['resumedAt'][0] === null && $changes['resumedAt'][1] !== null){
            $projet = $projetSuspendPeriod->getProjet();

            $activity = new Activity();
            $activity
                ->setType(self::getType())
                ->setDatetime(new \DateTime($projetSuspendPeriod->getResumedAt()->format('Y-m-d'). " " .(new \DateTime())->format('H:i:s')))
                ->setParameters([
                    'projet' => intval($projet->getId()),
                    'resumedBy' => intval($this->userContext->getSocieteUser()->getId()),
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
}

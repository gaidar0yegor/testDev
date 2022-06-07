<?php

namespace App\Activity\Type;

use App\Activity\ActivityInterface;
use App\Entity\Activity;
use App\Entity\Projet;
use App\Entity\ProjetActivity;
use App\Entity\ProjetPlanning;
use App\Entity\SocieteUser;
use App\Entity\SocieteUserActivity;
use App\Service\EntityLink\EntityLinkService;
use App\MultiSociete\UserContext;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjetPlanningCreatedActivity implements ActivityInterface
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
        return 'projet_planning_created';
    }

    public static function getFilterType(): string
    {
        return 'projet_planning';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'updatedBy',
            'projet',
        ]);

        $resolver->setAllowedTypes('updatedBy', 'integer');
        $resolver->setAllowedTypes('projet', 'integer');
    }

    public function render(array $activityParameters, Activity $activity): string
    {
        return sprintf(
            '%s %s a mis à jour la planification du projet %s.',
            '<i class="fa fa-tasks" aria-hidden="true"></i>',
            $this->entityLinkService->generateLink(SocieteUser::class, $activityParameters['updatedBy']),
            $this->entityLinkService->generateLink(Projet::class, $activityParameters['projet'])
        );
    }

    public function postPersist(ProjetPlanning $projetPlanning, LifecycleEventArgs $args): void
    {
        $this->createActivity($args->getEntityManager(), $projetPlanning);
    }

    public function postUpdate(ProjetPlanning $projetPlanning, LifecycleEventArgs $args): void
    {

        $this->createActivity($args->getEntityManager(), $projetPlanning);
    }

    private function createActivity(EntityManagerInterface $em, ProjetPlanning $projetPlanning): void
    {
        $beginOfDay = (new \DateTime())->setTimestamp(strtotime("today", time()));
        $endOfDay = (new \DateTime())->setTimestamp(strtotime("tomorrow", $beginOfDay->getTimestamp()) - 1);
        $parameters = [
            'updatedBy' => intval($this->userContext->getSocieteUser()->getId()),
            'projet' => intval($projetPlanning->getProjet()->getId()),
        ];

        $lastActivities = $em->getRepository(Activity::class)->getByCreteria(self::getType(), $parameters, $beginOfDay, $endOfDay);

        if (count($lastActivities) === 0){
            $activity = new Activity();
            $activity
                ->setType(self::getType())
                ->setParameters($parameters)
            ;

            $projetActivity = new ProjetActivity();
            $projetActivity
                ->setProjet($projetPlanning->getProjet())
                ->setActivity($activity)
            ;

            $societeUserActivity = new SocieteUserActivity();
            $societeUserActivity
                ->setSocieteUser($this->userContext->getSocieteUser())
                ->setActivity($activity)
            ;

            $em->persist($activity);
            $em->persist($projetActivity);
            $em->persist($societeUserActivity);
            $em->flush();
        }
    }
}

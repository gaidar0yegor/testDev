<?php

namespace App\Activity\Type;

use App\Activity\ActivityInterface;
use App\Entity\Activity;
use App\Entity\Projet;
use App\Entity\ProjetPlanningTask;
use App\Entity\SocieteUser;
use App\Entity\SocieteUserNotification;
use App\Service\EntityLink\EntityLinkService;
use App\MultiSociete\UserContext;
use App\SocieteProduct\Product\ProductPrivileges;
use App\SocieteProduct\ProductPrivilegeCheker;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlanningTaskCompleted implements ActivityInterface
{
    private EntityManagerInterface $em;

    private EntityLinkService $entityLinkService;

    private UserContext $userContext;

    public function __construct(EntityLinkService $entityLinkService, EntityManagerInterface $em, UserContext $userContext)
    {
        $this->em = $em;
        $this->entityLinkService = $entityLinkService;
        $this->userContext = $userContext;
    }

    public static function getType(): string
    {
        return 'projet_planning_task_completed';
    }

    public static function getFilterType(): string
    {
        return 'projet_planning';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'societeUser',
            'task',
            'projet'
        ]);

        $resolver->setAllowedTypes('societeUser', 'integer');
        $resolver->setAllowedTypes('task', 'integer');
        $resolver->setAllowedTypes('projet', 'integer');
    }

    public function render(array $activityParameters, Activity $activity): string
    {
        return sprintf(
            '%s %s a marqué la tâche %s du projet %s comme terminée.',
            '<i class="fa fa-tasks" aria-hidden="true"></i>',
            $this->entityLinkService->generateLink(SocieteUser::class, $activityParameters['societeUser']),
            $this->entityLinkService->generateLink(ProjetPlanningTask::class, $activityParameters['task']),
            $this->entityLinkService->generateLink(Projet::class, $activityParameters['projet'])
        );
    }

    public function preUpdate(ProjetPlanningTask $projetPlanningTask, PreUpdateEventArgs $args): void
    {
        $changes = $args->getEntityManager()->getUnitOfWork()->getEntityChangeSet($projetPlanningTask);

        $em = $args->getEntityManager();

        if (!isset($changes['progress'])) {
            return;
        }

        if ($changes['progress'][1] != 1) {
            $projetPlanningTask->setEndDateReal(null);
            $em->persist($projetPlanningTask);
            return;
        }

        $projetPlanningTask->setEndDateReal(new \DateTime());
        $em->persist($projetPlanningTask);
    }

    public function postUpdate(ProjetPlanningTask $projetPlanningTask, LifecycleEventArgs $args): void
    {
        $changes = $args->getEntityManager()->getUnitOfWork()->getEntityChangeSet($projetPlanningTask);

        if (!isset($changes['progress']) || $changes['progress'][1] != 1) {
            return;
        }

        $societeUser = $this->userContext->getSocieteUser();
        $projet = $projetPlanningTask->getProjet();

        $activity = new Activity();
        $activity
            ->setType(self::getType())
            ->setParameters([
                'societeUser' => intval($societeUser->getId()),
                'task' => intval($projetPlanningTask->getId()),
                'projet' => intval($projet->getId())
            ])
        ;

        $this->em->persist($activity);

        $chefDeProjet = $projet->getChefDeProjet();
        $userNotification = SocieteUserNotification::create($activity, $chefDeProjet);
        $this->em->persist($userNotification);

        foreach ($projetPlanningTask->getParticipants() as $participant){
            if ($participant->getSocieteUser() !== $chefDeProjet){
                $userNotification = SocieteUserNotification::create($activity, $participant->getSocieteUser());
                $this->em->persist($userNotification);
            }
        }

        $this->em->flush();
    }
}

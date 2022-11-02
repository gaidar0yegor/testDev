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
use App\Service\EntityLink\EntityLinkService;
use App\MultiSociete\UserContext;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FaitMarquantModifiedActivity implements ActivityInterface
{
    private EntityManagerInterface $em;

    private EntityLinkService $entityLinkService;

    private UserContext $userContext;

    public function __construct(EntityLinkService $entityLinkService, UserContext $userContext, EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->entityLinkService = $entityLinkService;
        $this->userContext = $userContext;
    }

    public static function getType(): string
    {
        return 'fait_marquant_modified';
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
            'modifiedBy',
            'faitMarquant',
        ]);

        $resolver->setAllowedTypes('projet', 'integer');
        $resolver->setAllowedTypes('createdBy', 'integer');
        $resolver->setAllowedTypes('modifiedBy', 'integer');
        $resolver->setAllowedTypes('faitMarquant', 'integer');
    }

    public function render(array $activityParameters, Activity $activity): string
    {
        $faitMarquant = $this->em->getRepository(FaitMarquant::class)->find($activityParameters['faitMarquant']);
        if (is_object($faitMarquant) && $faitMarquant->getTrashedBy() !== null && $faitMarquant->getTrashedAt() !== null){
            return '';
        }
        if ($activityParameters['createdBy'] === $activityParameters['modifiedBy']) {
            return sprintf(
                '%s %s a modifié son fait marquant %s sur le projet %s.',
                '<i class="fa fa-edit" aria-hidden="true"></i>',
                $this->entityLinkService->generateLink(SocieteUser::class, $activityParameters['modifiedBy']),
                $this->entityLinkService->generateLink(FaitMarquant::class, $activityParameters['faitMarquant']),
                $this->entityLinkService->generateLink(Projet::class, $activityParameters['projet'])
            );
        }

        return sprintf(
            '%s %s a modifié le fait marquant %s créé par %s sur le projet %s.',
            '<i class="fa fa-edit" aria-hidden="true"></i>',
            $this->entityLinkService->generateLink(SocieteUser::class, $activityParameters['modifiedBy']),
            $this->entityLinkService->generateLink(FaitMarquant::class, $activityParameters['faitMarquant']),
            $this->entityLinkService->generateLink(SocieteUser::class, $activityParameters['createdBy']),
            $this->entityLinkService->generateLink(Projet::class, $activityParameters['projet'])
        );
    }

    public function postUpdate(FaitMarquant $faitMarquant, LifecycleEventArgs $args): void
    {
        $em = $args->getEntityManager();
        $changes = $em->getUnitOfWork()->getEntityChangeSet($faitMarquant);

        if (key_exists('trashedAt',$changes) && key_exists('trashedBy',$changes) && count($changes) === 2){
            return;
        }

        $modifiedBy = $this->userContext->getSocieteUser();

        $activity = new Activity();
        $activity
            ->setType(self::getType())
            ->setParameters([
                'projet' => intval($faitMarquant->getProjet()->getId()),
                'createdBy' => intval($faitMarquant->getCreatedBy()->getId()),
                'modifiedBy' => intval($modifiedBy->getId()),
                'faitMarquant' => intval($faitMarquant->getId()),
            ])
        ;

        // START:: Verifier s'il a déjà une notification dans le même jour

        $oldActivities = $em->getRepository(Activity::class)->getByCreteria(
            $activity->getType(),
            $activity->getParameters(),
            new \DateTime('today midnight')
        );
        if (count($oldActivities) > 0){
            return;
        }

        // END:: Verifier s'il a déjà une notification dans le même jour

        $societeUserActivity = new SocieteUserActivity();
        $societeUserActivity
            ->setSocieteUser($modifiedBy)
            ->setActivity($activity)
        ;

        $projetActivity = new ProjetActivity();
        $projetActivity
            ->setProjet($faitMarquant->getProjet())
            ->setActivity($activity)
        ;

        $em = $args->getEntityManager();

        if ($faitMarquant->getCreatedBy() !== $modifiedBy) {
            $societeUserNotification = SocieteUserNotification::create($activity, $faitMarquant->getCreatedBy());
            $em->persist($societeUserNotification);
        }

        $em->persist($activity);
        $em->persist($societeUserActivity);
        $em->persist($projetActivity);
    }
}

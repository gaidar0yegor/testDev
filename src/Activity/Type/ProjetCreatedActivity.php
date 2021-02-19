<?php

namespace App\Activity\Type;

use App\Activity\ActivityInterface;
use App\Entity\Activity;
use App\Entity\Projet;
use App\Entity\ProjetActivity;
use App\Entity\User;
use App\Entity\UserActivity;
use App\Exception\RdiException;
use App\Service\EntityLink\EntityLinkService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjetCreatedActivity implements ActivityInterface
{
    private EntityLinkService $entityLinkService;

    public function __construct(EntityLinkService $entityLinkService)
    {
        $this->entityLinkService = $entityLinkService;
    }

    public static function getType(): string
    {
        return 'projet_created';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'projet',
            'createdBy',
        ]);

        $resolver->setAllowedTypes('projet', 'integer');
        $resolver->setAllowedTypes('createdBy', 'integer');
    }

    public function render(array $activityParameters, string $activityType): string
    {
        return sprintf(
            '%s %s a créé le projet %s.',
            '<i class="fa fa-plus" aria-hidden="true"></i>',
            $this->entityLinkService->generateLink(User::class, $activityParameters['createdBy']),
            $this->entityLinkService->generateLink(Projet::class, $activityParameters['projet'])
        );
    }

    public function postPersist(Projet $projet, LifecycleEventArgs $args): ?Activity
    {
        try {
            $chefDeProjet = $projet->getChefDeProjet();
        } catch (RdiException $e) {
            return null;
        }

        $activity = new Activity();
        $activity
            ->setType(self::getType())
            ->setParameters([
                'projet' => intval($projet->getId()),
                'createdBy' => intval($chefDeProjet->getId()),
            ])
        ;

        $userActivity = new UserActivity();
        $userActivity
            ->setUser($chefDeProjet)
            ->setActivity($activity)
        ;

        $projetActivity = new ProjetActivity();
        $projetActivity
            ->setProjet($projet)
            ->setActivity($activity)
        ;

        $em = $args->getEntityManager();

        $em->persist($activity);
        $em->persist($userActivity);
        $em->persist($projetActivity);
        $em->flush();

        return $activity;
    }
}

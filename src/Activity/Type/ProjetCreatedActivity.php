<?php

namespace App\Activity\Type;

use App\Activity\ActivityEvent;
use App\Activity\ActivityHandlerInterface;
use App\Entity\Activity;
use App\Entity\Projet;
use App\Entity\ProjetActivity;
use App\Entity\User;
use App\Entity\UserActivity;
use App\Exception\RdiException;
use App\Service\EntityLink\EntityLinkService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjetCreatedActivity implements ActivityHandlerInterface
{
    public const TYPE = 'projet_created';

    private EntityLinkService $entityLinkService;

    public function __construct(EntityLinkService $entityLinkService)
    {
        $this->entityLinkService = $entityLinkService;
    }

    public static function getType(): string
    {
        return self::TYPE;
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

    public function render(array $activityParameters): string
    {
        return sprintf(
            '%s %s a créé le projet %s.',
            '<i class="fa fa-plus" aria-hidden="true"></i>',
            $this->entityLinkService->generateLink(User::class, $activityParameters['createdBy']),
            $this->entityLinkService->generateLink(Projet::class, $activityParameters['projet'])
        );
    }

    public function getSubscribedEvent(): array
    {
        return [Projet::class, ActivityEvent::CREATED];
    }

    /**
     * @param Projet $projet
     */
    public function onEvent($projet, EntityManagerInterface $em): ?Activity
    {
        try {
            $chefDeProjet = $projet->getChefDeProjet();
        } catch (RdiException $e) {
            return null;
        }

        $activity = new Activity();
        $activity
            ->setType(self::TYPE)
            ->setParameters([
                'projet' => intval($projet->getId()),
                'createdBy' => intval($projet->getChefDeProjet()->getId()),
            ])
        ;

        $userActivity = new UserActivity();
        $userActivity
            ->setUser($projet->getChefDeProjet())
            ->setActivity($activity)
        ;

        $projetActivity = new ProjetActivity();
        $projetActivity
            ->setProjet($projet)
            ->setActivity($activity)
        ;

        $em->persist($activity);
        $em->persist($userActivity);
        $em->persist($projetActivity);
        $em->flush();

        return $activity;
    }
}

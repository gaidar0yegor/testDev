<?php

namespace App\Activity\Type;

use App\Activity\ActivityInterface;
use App\Entity\Activity;
use App\Entity\FichierProjet;
use App\Entity\Projet;
use App\Entity\ProjetActivity;
use App\Entity\SocieteUser;
use App\Notification\Event\FichierProjetAddedEvent;
use App\Service\EntityLink\EntityLinkService;
use App\MultiSociete\UserContext;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FichierProjetAddedActivity implements ActivityInterface, EventSubscriberInterface
{
    private EntityManagerInterface $em;

    private EntityLinkService $entityLinkService;

    private UserContext $userContext;

    private UrlGeneratorInterface $urlGenerator;

    public function __construct(
        EntityManagerInterface $em,
        EntityLinkService $entityLinkService,
        UserContext $userContext,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->em = $em;
        $this->entityLinkService = $entityLinkService;
        $this->userContext = $userContext;
        $this->urlGenerator = $urlGenerator;
    }


    public static function getType(): string
    {
        return 'fichier_projet_added';
    }

    public static function getFilterType(): string
    {
        return 'fichier_projet';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'uploadedBy',
            'projet',
            'fichierProjet'
        ]);

        $resolver->setAllowedTypes('uploadedBy', 'integer');
        $resolver->setAllowedTypes('projet', 'integer');
        $resolver->setAllowedTypes('fichierProjet', 'integer');
    }

    public function render(array $activityParameters, Activity $activity): string
    {
        return sprintf(
            "%s %s a ajouté le fichier %s dans le projet %s.",
            '<i class="fa fa-upload" aria-hidden="true"></i>',
            $this->entityLinkService->generateLink(SocieteUser::class, $activityParameters['uploadedBy']),
            $this->entityLinkService->generateLink(FichierProjet::class, $activityParameters['fichierProjet']),
            $this->entityLinkService->generateLink(Projet::class, $activityParameters['projet'])
        );
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FichierProjetAddedEvent::class => 'createActivity',
        ];
    }

    public function createActivity(FichierProjetAddedEvent $event): void
    {
        $fichierProjet = $event->getFichierProjet();
        $projet = $event->getProjet();
        $uploadedBy = $event->getUploadedBy();


        $activity = new Activity();
        $activity
            ->setType(self::getType())
            ->setParameters([
                'uploadedBy' => intval($uploadedBy->getId()),
                'projet' => intval($projet->getId()),
                'fichierProjet' => intval($fichierProjet->getId())
            ])
        ;

        $projetActivity = new ProjetActivity();
        $projetActivity
            ->setActivity($activity)
            ->setProjet($projet)
        ;

        $this->em->persist($activity);
        $this->em->persist($projetActivity);

        $this->em->flush();
    }
}

<?php

namespace App\Listener;

use App\Entity\FichierProjet;
use App\Service\FichierProjetService;
use Doctrine\ORM\Event\LifecycleEventArgs;

class FichierProjetListener
{
    private FichierProjetService $fichierProjetService;

    public function __construct(FichierProjetService $fichierProjetService)
    {
        $this->fichierProjetService = $fichierProjetService;
    }

    public function prePersist(FichierProjet $fichierProjet, LifecycleEventArgs $args): void
    {
        $this->fichierProjetService->setAccessChoices($fichierProjet, $fichierProjet->getProjet(), $fichierProjet->getAccessesChoices());
    }

    public function postUpdate(FichierProjet $fichierProjet, LifecycleEventArgs $args): void
    {
        $changes = $args->getEntityManager()->getUnitOfWork()->getEntityChangeSet($fichierProjet);

        if (!isset($changes['accessesChoices'])) {
            return;
        }

        $fichierProjet->getSocieteUsers()->map(function($societeUser) use ($fichierProjet) {
            $fichierProjet->removeSocieteUser($societeUser); return true;
        });

        $this->fichierProjetService->setAccessChoices($fichierProjet, $fichierProjet->getProjet(), $fichierProjet->getAccessesChoices());

        $em = $args->getEntityManager();
        $em->persist($fichierProjet);
        $em->flush();
    }
}

<?php

namespace App\Listener;

use App\Entity\FichierProjet;
use App\File\FileHandler\ProjectFileHandler;
use App\Service\FichierProjetService;
use Doctrine\ORM\Event\LifecycleEventArgs;

class FichierProjetListener
{
    private FichierProjetService $fichierProjetService;
    private ProjectFileHandler $fileHandler;

    public function __construct(FichierProjetService $fichierProjetService, ProjectFileHandler $fileHandler)
    {
        $this->fichierProjetService = $fichierProjetService;
        $this->fileHandler = $fileHandler;
    }

    public function prePersist(FichierProjet $fichierProjet, LifecycleEventArgs $args): void
    {
        $this->fichierProjetService->setAccessChoices($fichierProjet, $fichierProjet->getProjet(), $fichierProjet->getAccessesChoices());
    }

    public function postUpdate(FichierProjet $fichierProjet, LifecycleEventArgs $args): void
    {
        $changes = $args->getEntityManager()->getUnitOfWork()->getEntityChangeSet($fichierProjet);

        $em = $args->getEntityManager();

        if (isset($changes['accessesChoices'])){
            $fichierProjet->getSocieteUsers()->map(function($societeUser) use ($fichierProjet) {
                $fichierProjet->removeSocieteUser($societeUser); return true;
            });

            $this->fichierProjetService->setAccessChoices($fichierProjet, $fichierProjet->getProjet(), $fichierProjet->getAccessesChoices());
            $em->persist($fichierProjet);
            $em->flush();
        }

        if (isset($changes['dossierFichierProjet']) && null === $fichierProjet->getFichier()->getExternalLink()){
            $oldRelativeFilePath = $fichierProjet->getRelativeProjetLocationPath() .
                ($changes['dossierFichierProjet'][0] === null ? "" : ($changes['dossierFichierProjet'][0])->getNomMd5() . "/") .
                $fichierProjet->getFichier()->getNomMd5();

            $this->fileHandler->replace($fichierProjet,$oldRelativeFilePath);
        }
    }
}

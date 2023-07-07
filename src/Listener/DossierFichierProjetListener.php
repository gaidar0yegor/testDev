<?php

namespace App\Listener;

use App\Entity\DossierFichierProjet;
use App\Entity\Projet;
use Doctrine\ORM\Event\LifecycleEventArgs;
use League\Flysystem\FilesystemInterface;

class DossierFichierProjetListener
{
    private FilesystemInterface $storage;

    public function __construct(FilesystemInterface $projectFilesStorage)
    {
        $this->storage = $projectFilesStorage;
    }

    public function postRemove(DossierFichierProjet $dossierFichierProjet, LifecycleEventArgs $args): void
    {
        $changes = $args->getEntityManager()->getUnitOfWork()->getEntityChangeSet($dossierFichierProjet);

        if (!isset($changes['projet'])){
            return;
        }

        $projet = $changes['projet'][0];

        if (!$projet instanceof Projet){
            return;
        }

        $this->storage->deleteDir("{$projet->getSociete()->getId()}/{$projet->getId()}/{$dossierFichierProjet->getNomMd5()}");
    }
}

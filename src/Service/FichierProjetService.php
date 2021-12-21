<?php

namespace App\Service;

use App\Entity\FichierProjet;
use App\Entity\Projet;
use App\Entity\SocieteUser;
use App\MultiSociete\UserContext;
use App\Security\Role\RoleProjet;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service avec des fonctions convernant les roles des user sur les projets.
 */
class FichierProjetService
{
    private EntityManagerInterface $em;
    private UserContext $userContext;

    public function __construct(EntityManagerInterface $em, UserContext $userContext)
    {
        $this->em = $em;
        $this->userContext = $userContext;
    }

    public static function getChoicesForAddFileAccess(Projet $projet): array
    {
        $choices = [
            'all' => 'all',
            RoleProjet::CDP => RoleProjet::CDP,
            RoleProjet::CONTRIBUTEUR => RoleProjet::CONTRIBUTEUR,
            RoleProjet::OBSERVATEUR => RoleProjet::OBSERVATEUR,
            RoleProjet::OBSERVATEUR_EXTERNE  => RoleProjet::OBSERVATEUR_EXTERNE,
        ];

        foreach ($projet->getProjetParticipants() as $projetParticipant){
            $choices[$projetParticipant->getSocieteUser()->getUser()->getShortname()] = $projetParticipant->getSocieteUser()->getId();
        }

        return $choices;
    }

    public function setAccessChoices(FichierProjet $fichierProjet, Projet $projet, array $accessChoices): void
    {
        $fichierProjet->setAccessesChoices($accessChoices);
        $fichierProjet->addSocieteUser($this->userContext->getSocieteUser());
        $fichierProjet->setIsAccessibleParObservateurExterne(in_array(RoleProjet::OBSERVATEUR_EXTERNE, $accessChoices) || in_array('all', $accessChoices));

        if (in_array('all', $accessChoices)) {
            foreach ($projet->getProjetParticipants() as $projetParticipant) {
                $fichierProjet->addSocieteUser($projetParticipant->getSocieteUser());
            }
        } else {
            foreach ($accessChoices as $accessChoice) {
                if ($accessChoice === RoleProjet::CDP) {
                    $fichierProjet->addSocieteUser($projet->getChefDeProjet());
                } elseif ($accessChoice === RoleProjet::CONTRIBUTEUR) {
                    foreach ($projet->getContributeurs() as $projetParticipant) {
                        $fichierProjet->addSocieteUser($projetParticipant->getSocieteUser());
                    }
                } elseif ($accessChoice === RoleProjet::OBSERVATEUR) {
                    foreach ($projet->getObservateurs() as $projetParticipant) {
                        $fichierProjet->addSocieteUser($projetParticipant->getSocieteUser());
                    }
                } elseif (gettype($accessChoice) === "integer") {
                    $fichierProjet->addSocieteUser($this->em->getRepository(SocieteUser::class)->find($accessChoice));
                }
            }
        }
    }
}

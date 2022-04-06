<?php

namespace App\Service;

use App\Entity\ProjetParticipant;
use App\Entity\SocieteUser;
use App\Repository\ProjetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Service pour pré-remplir une instance de SocieteUser
 * avec tous les ProjetParticipant possible
 * afin de l'utiliser dans le formulaire SocieteUserProjetsRolesType
 *
 * @see App\Form\SocieteUserProjetsRolesType
 */
class UserProjetAffectation
{
    private ProjetRepository $projetRepository;

    public function __construct(ProjetRepository $projetRepository)
    {
        $this->projetRepository = $projetRepository;
    }

    /**
     * Ajoute des instances de ProjetRepository dans la liste d'un UserSociete
     * pour chaque projet dont l'user ne participe pas encore,
     * avec un rôle à null.
     */
    public function addProjetsWithNoRole(SocieteUser $societeUser): void
    {
        $otherProjets = $this->projetRepository->findProjetsWhereUserHasNoRole($societeUser);

        foreach ($otherProjets as $otherProjet) {
            $societeUser->addProjetParticipant(ProjetParticipant::create(
                $societeUser,
                $otherProjet,
                null
            ));
        }
    }

    /**
     * Retire les instances de ProjetRepository dans la liste d'un UserSociete
     * dont le rôle est toujours à null.
     * Utile après la soumission d'un formulaire, quand on a retiré le rôle d'un user sur projet.
     *
     * @param ObjectManager $om If provided, persist or remove each $projetParticipant
     */
    public function clearProjetsWithNoRole(SocieteUser $societeUser, ObjectManager $om = null): void
    {
        foreach ($societeUser->getProjetParticipants() as $projetParticipant) {
            if (null === $projetParticipant->getRole()) {
                $societeUser->removeProjetParticipant($projetParticipant);

                if (null !== $om) {
                    $om->remove($projetParticipant);
                }
            } else {
                if (null !== $om) {
                    $om->persist($projetParticipant);
                }
            }
        }
    }
}

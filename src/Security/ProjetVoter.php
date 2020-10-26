<?php

namespace App\Security;

use App\Entity\Projet;
use App\Entity\User;
use App\Exception\RdiException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Security voter pour vérifier qu'un user
 * peut effectuer une action sur un projet donné.
 */
class ProjetVoter extends Voter
{
    private $authChecker;

    public function __construct(AuthorizationCheckerInterface $authChecker)
    {
        $this->authChecker = $authChecker;
    }

    /**
     * {@inheritDoc}
     */
    protected function supports($attribute, $subject): bool
    {
        return $subject instanceof Projet;
    }

    /**
     * {@inheritDoc}
     *
     * @param string $attribute
     * @param Projet $subject
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        // Empêche tous les accès aux projets des autres sociétés
        if (!$this->isSameSociete($subject, $token->getUser())) {
            return false;
        }

        // L'admin a tous les droits sur tous les projets
        if ($this->authChecker->isGranted('ROLE_FO_ADMIN')) {
            return true;
        }

        // Le chef de projet a tous les droits sur son propre projet
        if ($this->isUserProjetCdp($subject, $token->getUser())) {
            return true;
        }

        switch ($attribute) {
            case 'view':
                // L'utilisateur peut voir le projet s'il a un rôle dessus
                return $this->isParticipant($subject, $token->getUser());

            case 'edit':
            case 'delete':
                // L'utilisateur ne peut pas modifier les infos du projet, ni supprimer le projet
                return false;

            default:
                throw new RdiException(sprintf('Unexpected attribute "%s"', $attribute));
        }
    }

    private function isSameSociete(Projet $projet, User $user): bool
    {
        return $projet->getChefDeProjet()->getSociete() === $user->getSociete();
    }

    private function isUserProjetCdp(Projet $projet, User $user): bool
    {
        return $projet->getChefDeProjet() === $user;
    }

    private function isParticipant(Projet $projet, User $user): bool
    {
        foreach ($projet->getProjetParticipants() as $projetParticipant) {
            if ($projetParticipant->getUser() === $user) {
                return true;
            }
        }

        return false;
    }
}

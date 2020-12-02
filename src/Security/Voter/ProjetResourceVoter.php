<?php

namespace App\Security\Voter;

use App\Entity\FaitMarquant;
use App\Entity\Projet;
use App\Entity\User;
use App\Exception\RdiException;
use App\ProjetResourceInterface;
use App\Role;
use App\Service\ParticipantService;
use App\Service\SocieteChecker;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Security voter pour vérifier qu'un user
 * peut agir sur une ressource du projet ou pas.
 * Exemple de ressource : fait marquant, fichier.
 */
class ProjetResourceVoter extends Voter
{
    private $authChecker;

    private $participantService;

    private $societeChecker;

    public function __construct(
        AuthorizationCheckerInterface $authChecker,
        ParticipantService $participantService,
        SocieteChecker $societeChecker
    ) {
        $this->authChecker = $authChecker;
        $this->participantService = $participantService;
        $this->societeChecker = $societeChecker;
    }

    /**
     * {@inheritDoc}
     */
    protected function supports($attribute, $subject): bool
    {
        if ($subject instanceof Projet && ProjetResourceInterface::CREATE === $attribute) {
            return true;
        }

        return $subject instanceof ProjetResourceInterface && in_array($attribute, [
            ProjetResourceInterface::VIEW,
            ProjetResourceInterface::EDIT,
            ProjetResourceInterface::DELETE,
        ]);
    }

    /**
     * {@inheritDoc}
     *
     * @param string $attribute
     * @param Projet|ProjetResourceInterface $subject
     * @param TokenInterface $token
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        if (ProjetResourceInterface::CREATE === $attribute) {
            return $this->userCanCreateResourceOnProjet($token->getUser(), $subject);
        }

        return $this->userCanDo($token->getUser(), $subject, $attribute);
    }

    public function userCanCreateResourceOnProjet(User $user, Projet $projet): bool
    {
        // User ne peut pas créer de ressource sur un projet d'une société autre que la sienne
        if (!$this->societeChecker->isSameSociete($projet, $user)) {
            return false;
        }

        // L'admin de la société peut créer des ressources sur tous les projets de sa propre société
        if ($this->authChecker->isGranted('ROLE_FO_ADMIN')) {
            return true;
        }

        // User doit être au moins contributeur sur ce projet
        if (!$this->participantService->hasRoleOnProjet($user, $projet, Role::CONTRIBUTEUR)) {
            return false;
        }

        return true;
    }

    public function userCanDo(User $user, ProjetResourceInterface $resource, string $action): bool
    {
        // User ne peut pas modifier les ressources sur un projet d'une société autre que la sienne
        if (!$this->societeChecker->isSameSociete($resource->getProjet(), $user)) {
            return false;
        }

        // L'admin de la société peut modifier les ressources de tous les projets de sa propre société
        if ($this->authChecker->isGranted('ROLE_FO_ADMIN')) {
            return true;
        }

        $userRole = $this->participantService->getRoleOfUserOnProjet($user, $resource->getProjet());

        // Le chef de projet peut tout faire sur les ressources de son propre projet
        if ($this->participantService->hasRole($userRole, Role::CDP)) {
            return true;
        }

        // Le contributeur qui a créé la ressource peut l'éditer
        if (
            $this->participantService->hasRole($userRole, Role::CONTRIBUTEUR)
            && $resource->getOwner() === $user
        ) {
            return true;
        }

        // L'observateur peut voir les ressources
        if (
            $this->participantService->hasRole($userRole, Role::OBSERVATEUR)
            && $action === ProjetResourceInterface::VIEW
        ) {
            return true;
        }

        // Sinon on refuse l'action.
        return false;
    }
}

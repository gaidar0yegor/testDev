<?php

namespace App\Security\Voter;

use App\Entity\Projet;
use App\Entity\SocieteUser;
use App\ProjetResourceInterface;
use App\Security\Role\RoleProjet;
use App\Security\Role\RoleSociete;
use App\Service\ParticipantService;
use App\Service\SocieteChecker;
use App\MultiSociete\UserContext;
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

    private UserContext $userContext;

    private $societeChecker;

    public function __construct(
        AuthorizationCheckerInterface $authChecker,
        ParticipantService $participantService,
        UserContext $userContext,
        SocieteChecker $societeChecker
    ) {
        $this->authChecker = $authChecker;
        $this->participantService = $participantService;
        $this->userContext = $userContext;
        $this->societeChecker = $societeChecker;
    }

    /**
     * {@inheritDoc}
     */
    protected function supports($attribute, $subject): bool
    {
        if ($subject instanceof Projet && !$subject->getIsSuspended() && ProjetResourceInterface::CREATE === $attribute) {
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
        $societeUser = $this->userContext->getSocieteUser();

        if (ProjetResourceInterface::CREATE === $attribute) {
            return $this->userCanCreateResourceOnProjet($societeUser, $subject);
        }

        return $this->userCanDo($societeUser, $subject, $attribute);
    }

    public function userCanCreateResourceOnProjet(SocieteUser $societeUser, Projet $projet): bool
    {
        // User ne peut pas créer de ressource sur un projet d'une société autre que la sienne
        if (!$this->societeChecker->isSameSociete($projet, $societeUser)) {
            return false;
        }

        // L'admin de la société peut créer des ressources sur tous les projets de sa propre société
        if ($this->authChecker->isGranted(RoleSociete::ADMIN)) {
            return true;
        }

        if ($projet->getIsSuspended()) {
            return false;
        }

        // User doit être au moins contributeur sur ce projet
        if (!$this->participantService->hasRoleOnProjet($societeUser, $projet, RoleProjet::CONTRIBUTEUR)) {
            return false;
        }

        return true;
    }

    public function userCanDo(SocieteUser $societeUser, ProjetResourceInterface $resource, string $action): bool
    {
        // User ne peut pas modifier les ressources sur un projet d'une société autre que la sienne
        if (!$this->societeChecker->isSameSociete($resource->getProjet(), $societeUser)) {
            return false;
        }

        if (
            ($action === ProjetResourceInterface::CREATE || $action === ProjetResourceInterface::EDIT || $action === ProjetResourceInterface::DELETE) &&
            $resource->getProjet()->getIsSuspended()
        ){
            return false;
        }

        // L'admin de la société peut modifier les ressources de tous les projets de sa propre société
        if ($this->authChecker->isGranted(RoleSociete::ADMIN)) {
            return true;
        }

        $userRole = $this->participantService->getRoleOfUserOnProjet($societeUser, $resource->getProjet());

        // Les user n'étant pas participant sur le projet ne peuvent rien faire
        if (null === $userRole) {
            return false;
        }

        // Le chef de projet peut tout faire sur les ressources de son propre projet
        if (RoleProjet::hasRole($userRole, RoleProjet::CDP)) {
            return true;
        }

        // Le contributeur qui a créé la ressource peut l'éditer
        if (
            RoleProjet::hasRole($userRole, RoleProjet::CONTRIBUTEUR)
            && $resource->getOwner() === $societeUser
        ) {
            return true;
        }

        // L'observateur peut voir les ressources
        if (
            RoleProjet::hasRole($userRole, RoleProjet::OBSERVATEUR)
            && $action === ProjetResourceInterface::VIEW
        ) {
            return true;
        }

        // Sinon on refuse l'action.
        return false;
    }
}

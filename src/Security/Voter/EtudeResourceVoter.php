<?php

namespace App\Security\Voter;

use App\Entity\LabApp\Etude;
use App\Entity\LabApp\UserBook;
use App\EtudeResourceInterface;
use App\MultiSociete\UserContext;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Security voter pour vérifier qu'un user
 * peut agir sur une ressource de l'étude ou pas.
 * Exemple de ressource : note, fichier.
 */
class EtudeResourceVoter extends Voter
{
    private UserContext $userContext;

    public function __construct(UserContext $userContext)
    {
        $this->userContext = $userContext;
    }

    /**
     * {@inheritDoc}
     */
    protected function supports($attribute, $subject): bool
    {
        if ($subject instanceof Etude && EtudeResourceInterface::CREATE === $attribute) {
            return true;
        }

        return $subject instanceof EtudeResourceInterface && in_array($attribute, [
                EtudeResourceInterface::VIEW,
                EtudeResourceInterface::EDIT,
                EtudeResourceInterface::DELETE,
            ]);
    }

    /**
     * {@inheritDoc}
     *
     * @param string $attribute
     * @param Etude|EtudeResourceInterface $subject
     * @param TokenInterface $token
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $userBook = $this->userContext->getUserBook();

        if (EtudeResourceInterface::CREATE === $attribute) {
            return $this->userCanCreateResourceOnEtude($userBook, $subject);
        }

        return $this->userCanDo($userBook, $subject, $attribute);
    }

    public function userCanCreateResourceOnEtude(UserBook $userBook, Etude $etude): bool
    {
        // il y a que le créateur de l'étude qui peut la gérer
        if ($userBook !== $etude->getOwner()) {
            return false;
        }

        return true;
    }

    public function userCanDo(UserBook $userBook, EtudeResourceInterface $resource, string $action): bool
    {
        // il y a que le créateur de l'étude qui peut ajouter une ressource
        if ($userBook !== $resource->getOwner()) {
            return false;
        }

        return true;
    }
}

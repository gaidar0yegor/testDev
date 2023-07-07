<?php

namespace App\Security\Voter;

use App\Entity\LabApp\Etude;
use App\Entity\LabApp\UserBook;
use App\Service\ParticipantService;
use App\Service\SocieteChecker;
use App\MultiSociete\UserContext;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Security voter pour vérifier qu'un user
 * peut effectuer une action sur une étude donné.
 */
class EtudeVoter extends Voter
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
        return $subject instanceof Etude && in_array($attribute, [
            'view',
            'edit',
            'delete',
        ]);
    }

    /**
     * {@inheritDoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        return $this->UserBookCan($attribute, $this->userContext->getUserBook(), $subject);
    }

    private function UserBookCan(string $action, UserBook $userBook, Etude $etude): bool
    {
        // Le Owner de l'étude a tous les droits sur toutes ses études
        if ($userBook === $etude->getOwner()) {
            return true;
        }

        switch ($action) {
            case 'view':
                return true;
            default:
                return false;
        }
    }
}

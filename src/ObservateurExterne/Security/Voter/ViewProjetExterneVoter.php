<?php

namespace App\ObservateurExterne\Security\Voter;

use App\Entity\Projet;
use App\MultiSociete\UserContext;
use App\Repository\ProjetObservateurExterneRepository;
use RuntimeException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Security voter pour vérifier qu'un user
 * peut effectuer une action sur un projet donné.
 */
class ViewProjetExterneVoter extends Voter
{
    public const VIEW = 'VIEW_PROJET_EXTERNE';

    private UserContext $userContext;

    private ProjetObservateurExterneRepository $projetObservateurExterneRepository;

    public function __construct(
        UserContext $userContext,
        ProjetObservateurExterneRepository $projetObservateurExterneRepository
    ) {
        $this->userContext = $userContext;
        $this->projetObservateurExterneRepository = $projetObservateurExterneRepository;
    }

    /**
     * {@inheritDoc}
     */
    protected function supports($attribute, $subject): bool
    {
        return self::VIEW === $attribute;
    }

    /**
     * {@inheritDoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        if (!$subject instanceof Projet) {
            throw new RuntimeException('Expected an instance of Projet');
        }

        $access = $this->projetObservateurExterneRepository->findOneByUserAndProjet(
            $this->userContext->getUser(),
            $subject
        );

        if (null === $access) {
            throw new AccessDeniedException('Vous n\'êtes pas ou plus observateur externe sur ce projet.');
        }

        return true;
    }
}

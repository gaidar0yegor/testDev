<?php

namespace App\Localization;

use App\Entity\User;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Update interface locale once user updates his locale.
 */
class UserEntityListener
{
    private SessionInterface $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function postUpdate(User $user, LifecycleEventArgs $args): void
    {
        $changes = $args->getEntityManager()->getUnitOfWork()->getEntityChangeSet($user);

        if (!isset($changes['locale'])) {
            return;
        }

        $this->session->set('_locale', $changes['locale'][1]);
    }
}

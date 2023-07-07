<?php

namespace App\File;

use App\Entity\User;
use Doctrine\ORM\Event\LifecycleEventArgs;

class CreateDefaultAvatarOnNewUser
{
    private DefaultAvatarGenerator $defaultAvatarGenerator;

    public function __construct(DefaultAvatarGenerator $defaultAvatarGenerator)
    {
        $this->defaultAvatarGenerator = $defaultAvatarGenerator;
    }

    public function postPersist(User $user, LifecycleEventArgs $args): void
    {
        if (null !== $user->getAvatar()) {
            return;
        }

        $avatar = $this->defaultAvatarGenerator->generateDefaultAvatarFor($user);

        $user->setAvatar($avatar);

        $args->getEntityManager()->flush();

        // Required to prevent https://github.com/doctrine/orm/issues/7817 in functionnal tests
        $args->getEntityManager()->refresh($user);
    }
}

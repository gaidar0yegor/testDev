<?php

namespace App\MultiSociete\Exception;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Throwable;

/**
 * Exception thrown when trying to set a User::currentSocieteUser
 * with an incorrect SocieteUser instance because either:
 *      - SocieteUser has not yet User (still not answer invitation), so this case should not happen
 *      - SocieteUser is not related to the same user as the ne logged in, so cannot usurpate another user's access
 *      - SocieteUser enabled is false, so cannot switch to a disabled access
 */
class CurrentSocieteUserAccessDeniedException extends AccessDeniedException
{
}

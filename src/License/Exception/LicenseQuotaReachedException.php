<?php

namespace App\License\Exception;

use App\License\DTO\Quota;

/**
 * Specific exception thrown when a quota is about to be overflow, and block the action.
 */
class LicenseQuotaReachedException extends LicenseException
{
    public function __construct(string $limitedElement, Quota $quotaAfter)
    {
        parent::__construct(sprintf(
            'Vous ne pouvez pas faire cette action car ca dépasserait votre quota "%s" qui sera alors de %d sur %d.',
            $limitedElement,
            $quotaAfter->getCurrent(),
            $quotaAfter->getLimit()
        ));
    }
}

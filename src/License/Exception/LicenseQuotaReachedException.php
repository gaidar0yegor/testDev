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
            'Mettre à jour votre licence | Quota dépassé : %s %d sur %d . <a href="https://rdimanager.com/pages/contact.html" style="color: #007bff!important;" target="_blank">Nous contacter</a>',
            ucfirst($limitedElement),
            $quotaAfter->getCurrent(),
            $quotaAfter->getLimit()
        ));
    }
}

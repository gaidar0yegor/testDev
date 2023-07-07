<?php

namespace App\License\Exception;

use App\License\DTO\Quota;
use Throwable;

/**
 * Specific exception thrown when a quota is about to be overflow, and block the action.
 */
class OverflowQuotasException extends LicenseException
{
    /**
     * @param Quota[] $quotas Quotas that are in overflow
     */
    public function __construct(array $quotas, Throwable $previous = null)
    {
        $message = '';

        $message .= 'Votre accès est en lecture seule car';
        $message .= ' un ou plusieurs quotas de vos licenses actives ont été dépassés.';
        $message .= ' Veuillez ajouter une nouvelle license.';
        $message .= ' Les quotas dépassés sont :';

        foreach ($quotas as $quotaName => $quota) {
            $message .= sprintf(
                ' %s : %d (limite : %d) ;',
                $quotaName,
                $quota->getCurrent(),
                $quota->getLimit()
            );
        }

        parent::__construct($message, $previous);
    }
}

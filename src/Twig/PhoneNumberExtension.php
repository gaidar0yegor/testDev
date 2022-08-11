<?php

namespace App\Twig;

use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use Misd\PhoneNumberBundle\Templating\Helper\PhoneNumberHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Provides 'phone_number_rdi' twig filter
 * that display french phone number of users,
 * and does not crash when phone is null.
 */
class PhoneNumberExtension extends AbstractExtension
{
    private PhoneNumberHelper $phoneNumberHelper;

    public function __construct(PhoneNumberHelper $phoneNumberHelper)
    {
        $this->phoneNumberHelper = $phoneNumberHelper;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('phone_number_rdi', [$this, 'phoneNumberRdi']),
        ];
    }

    public function phoneNumberRdi(?PhoneNumber $phoneNumber): string
    {
        if (null === $phoneNumber) {
            return '';
        }

        return $this->phoneNumberHelper->format($phoneNumber);
    }
}

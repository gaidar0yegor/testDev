<?php

namespace App\Service;

use App\Entity\User;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\TexterInterface;

class SmsSender
{
    private TexterInterface $texter;

    private PhoneNumberUtil $phoneNumberUtil;

    public function __construct(TexterInterface $texter, PhoneNumberUtil $phoneNumberUtil)
    {
        $this->texter = $texter;
        $this->phoneNumberUtil = $phoneNumberUtil;
    }

    public function sendSms(User $to, string $message): bool
    {
        if (!$to->getTelephone()) {
            return false;
        }

        $phoneNumber = $this->phoneNumberUtil->format($to->getTelephone(), PhoneNumberFormat::E164);

        $sms = new SmsMessage($phoneNumber, $message);

        $this->texter->send($sms);

        return true;
    }
}

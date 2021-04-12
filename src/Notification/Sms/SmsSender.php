<?php

namespace App\Notification\Sms;

use libphonenumber\PhoneNumber;
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

    public function sendSms(PhoneNumber $phoneNumber, string $message): bool
    {
        $phoneNumber = $this->phoneNumberUtil->format($phoneNumber, PhoneNumberFormat::E164);

        $sms = new SmsMessage($phoneNumber, $message);

        $this->texter->send($sms);

        return true;
    }
}

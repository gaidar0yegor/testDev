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
        $phoneNumber = $this->phoneNumberUtil->format($phoneNumber, PhoneNumberFormat::INTERNATIONAL);

        $sms = new SmsMessage($phoneNumber, $this->removeAccents($message));

        $this->texter->send($sms);

        return true;
    }

    private function removeAccents(string $text): string
    {
        $search  = array('À', 'Á', 'Â', 'Ã', 'È', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ò', 'Ó', 'Ô', 'Õ', 'Ù', 'Ú', 'Û', 'Ý', 'á', 'â', 'ã', 'ç', 'ê', 'ë', 'í', 'î', 'ï', 'ð', 'ó', 'ô', 'õ', 'ú', 'û', 'ý', 'ÿ');
        $replace = array('A', 'A', 'A', 'A', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'Y', 'a', 'a', 'a', 'c', 'e', 'e', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'u', 'u', 'y', 'y');

        return str_replace($search, $replace, $text);
    }
}

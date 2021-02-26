<?php

namespace App\LicenseGeneration;

use App\License\DTO\License;
use App\License\Serializer\LicenseDecoder;
use Symfony\Component\Serializer\SerializerInterface;

class LicenseGeneration
{
    private SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function generateLicenseFile(License $license): string
    {
        return $this->serializer->serialize($license, LicenseDecoder::FORMAT);
    }
}

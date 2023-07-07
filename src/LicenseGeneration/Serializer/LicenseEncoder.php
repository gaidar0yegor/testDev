<?php

namespace App\LicenseGeneration\Serializer;

use App\License\Serializer\LicenseDecoder;
use App\LicenseGeneration\Encryption;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Twig\Environment;

/**
 * Encode une instance de License en un fichier de license signé numériquement.
 */
class LicenseEncoder implements EncoderInterface
{
    private const SEPARATOR = '-=-=-=-=-=-=-=-';

    private Encryption $encryption;

    private Environment $twig;

    public function __construct(
        Encryption $encryption,
        Environment $twig
    ) {
        $this->encryption = $encryption;
        $this->twig = $twig;
    }

    /**
     * @param array<string, string> $context
     */
    public function encode($data, string $format, array $context = [])
    {
        $jsonLicense = json_encode($data);

        $crypted = $this->encryption->privateEncrypt($jsonLicense);

        $encodedLicense = $this->twig->render('corp_app/license/license.txt.twig', [
            'license' => $data,
            'separator' => self::SEPARATOR,
            'rawData' => $crypted,
        ]);

        return $encodedLicense;
    }

    public function supportsEncoding(string $format)
    {
        return LicenseDecoder::FORMAT === $format;
    }
}

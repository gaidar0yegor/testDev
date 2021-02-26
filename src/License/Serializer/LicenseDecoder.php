<?php

namespace App\License\Serializer;

use App\License\Decryption;
use App\License\Exception\DecryptionException;
use DateTime;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;

/**
 * Encode une instance de License en un fichier de license signé numériquement.
 */
class LicenseDecoder implements DecoderInterface
{
    public const FORMAT = 'rdi_license';

    private const SEPARATOR = '-=-=-=-=-=-=-=-';

    private Decryption $decryption;

    public function __construct(Decryption $decryption)
    {
        $this->decryption = $decryption;
    }

    /**
     * @param array<string, string> $context
     */
    public function decode(string $data, string $format, array $context = [])
    {
        $licenseParts = explode(self::SEPARATOR, $data);
        $rawData = array_pop($licenseParts);
        $rawData = trim($rawData);

        try {
            $decrypted = $this->decryption->publicDecrypt($rawData);
        } catch (DecryptionException $e) {
            throw new UnexpectedValueException($e->getMessage(), 0, $e);
        }

        return json_decode($decrypted, true);
    }

    public function supportsDecoding(string $format)
    {
        return self::FORMAT === $format;
    }
}

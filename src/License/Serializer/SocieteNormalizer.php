<?php

namespace App\License\Serializer;

use App\Entity\Societe;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Custom normalizer of Societe instances for license serializer.
 * Only provide few Societe attributes (uuid, raisonSociale)
 */
class SocieteNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * @param array<string, string> $context
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        if (!$object instanceof Societe) {
            throw new InvalidArgumentException('This normalizer can only normalize societe isntances.');
        }

        return [
            'uuid' => $object->getUuid(),
            'raisonSociale' => $object->getRaisonSociale(),
        ];
    }

    /**
     * @param array<string, string> $context
     */
    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        $societe = new Societe();

        $societe
            ->setUuid(Uuid::fromString($data['uuid']))
            ->setRaisonSociale($data['raisonSociale'])
        ;

        return $societe;
    }

    public function supportsNormalization($data, string $format = null)
    {
        return LicenseDecoder::FORMAT === $format && $data instanceof Societe;
    }

    public function supportsDenormalization($data, string $type, string $format = null)
    {
        return LicenseDecoder::FORMAT === $format && Societe::class === $type;
    }
}

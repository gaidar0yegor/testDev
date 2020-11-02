<?php

namespace App\DTO;

use App\Entity\TempsPasse;
use App\Validator as AppAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Classe utilisée pour stocker les temps passé
 * sur un projet, par un user dans un mois.
 */
class ListeTempsPasses
{
    /**
     * @var TempsPasse[]
     *
     * @Assert\Valid
     * @AppAssert\TempsPassesValid
     */
    private $tempsPasses;

    /**
     * @param TempsPasse[] $tempsPasses
     */
    public function __construct(array $tempsPasses = [])
    {
        $this->tempsPasses = $tempsPasses;
    }

    /**
     * @return TempsPasse[]
     */
    public function getTempsPasses(): array
    {
        return $this->tempsPasses;
    }

    public function setTempsPasses(array $tempsPasses): self
    {
        $this->tempsPasses = $tempsPasses;

        return $this;
    }
}

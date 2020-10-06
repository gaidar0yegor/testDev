<?php

namespace App\Entity;

use App\Repository\ReferentielRdiRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ReferentielRdiRepository::class)
 */
class ReferentielRdi
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $mot_cle;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMotCle(): ?string
    {
        return $this->mot_cle;
    }

    public function setMotCle(string $mot_cle): self
    {
        $this->mot_cle = $mot_cle;

        return $this;
    }
}

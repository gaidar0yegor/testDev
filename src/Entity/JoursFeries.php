<?php

namespace App\Entity;

use App\Repository\JoursFeriesRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=JoursFeriesRepository::class)
 */
class JoursFeries
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     */
    private $date_jour;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateJour(): ?\DateTimeInterface
    {
        return $this->date_jour;
    }

    public function setDateJour(\DateTimeInterface $date_jour): self
    {
        $this->date_jour = $date_jour;

        return $this;
    }
}

<?php

namespace App\Entity;

use App\Repository\ProjetSuspendPeriodRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProjetSuspendPeriodRepository::class)
 */
class ProjetSuspendPeriod
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     */
    private $suspendedAt;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $resumedAt;

    /**
     * @ORM\ManyToOne(targetEntity=Projet::class, inversedBy="projetSuspendPeriods")
     * @ORM\JoinColumn(nullable=false)
     */
    private $projet;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSuspendedAt(): ?\DateTimeInterface
    {
        return $this->suspendedAt;
    }

    public function setSuspendedAt(\DateTimeInterface $suspendedAt): self
    {
        $this->suspendedAt = $suspendedAt;

        return $this;
    }

    public function getResumedAt(): ?\DateTimeInterface
    {
        return $this->resumedAt;
    }

    public function setResumedAt(?\DateTimeInterface $resumedAt): self
    {
        $this->resumedAt = $resumedAt;

        return $this;
    }

    public function getProjet(): ?Projet
    {
        return $this->projet;
    }

    public function setProjet(?Projet $projet): self
    {
        $this->projet = $projet;

        return $this;
    }
}

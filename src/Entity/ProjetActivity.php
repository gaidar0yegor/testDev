<?php

namespace App\Entity;

use App\Repository\ProjetActivityRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProjetActivityRepository::class)
 */
class ProjetActivity
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Projet::class, inversedBy="projetActivities")
     * @ORM\JoinColumn(nullable=false)
     */
    private $projet;

    /**
     * @ORM\ManyToOne(targetEntity=Activity::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $activity;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getActivity(): ?Activity
    {
        return $this->activity;
    }

    public function setActivity(?Activity $activity): self
    {
        $this->activity = $activity;

        return $this;
    }
}

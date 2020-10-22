<?php

namespace App\Entity;

use App\Repository\TempsPasseRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=TempsPasseRepository::class)
 * @UniqueEntity(
 *     fields={"user", "projet", "mois"},
 *     errorPath="pourcentage",
 *     message="Cet utilisateur ne peut pas créer un deuxième pourcentage sur ce projet et ce mois. Il faut modifier l'autre pourcentage plutôt."
 * )
 */
class TempsPasse
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * Mois sur lequel le temps a ete passe sur le projet
     *
     * @ORM\Column(type="date")
     */
    private $mois;

    /**
     * Pourcentage du temps passe sur le projet
     * De 0 a 100
     *
     * @ORM\Column(type="integer")
     */
    private $pourcentage;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="tempsPasses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Projet::class, inversedBy="tempsPasses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $projet;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPourcentage(): ?int
    {
        return $this->pourcentage;
    }

    public function setPourcentage(?int $pourcentage): self
    {
        $this->pourcentage = $pourcentage;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

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

    public function getMois(): ?\DateTimeInterface
    {
        return $this->mois;
    }

    public function setMois(\DateTimeInterface $mois): self
    {
        $this->mois = $mois;

        return $this;
    }
}

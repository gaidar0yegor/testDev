<?php

namespace App\Entity;

use App\HasSocieteInterface;
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
class TempsPasse implements HasSocieteInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * Pourcentage du temps passe sur le projet
     * De 0 a 100
     *
     * @ORM\Column(type="integer")
     */
    private $pourcentage;

    /**
     * @ORM\ManyToOne(targetEntity=Projet::class, inversedBy="tempsPasses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $projet;

    /**
     * @ORM\ManyToOne(targetEntity=Cra::class, inversedBy="tempsPasses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $cra;

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

    public function getProjet(): Projet
    {
        return $this->projet;
    }

    public function setProjet(Projet $projet): self
    {
        $this->projet = $projet;

        return $this;
    }

    public function getCra(): ?Cra
    {
        return $this->cra;
    }

    public function setCra(?Cra $cra): self
    {
        $this->cra = $cra;

        return $this;
    }

    public function getSociete(): ?Societe
    {
        return $this->projet->getSociete();
    }
}

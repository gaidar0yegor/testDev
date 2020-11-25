<?php

namespace App\Entity;

use App\Repository\CraRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Détermine quels jours un utilisateur a travaillé sur un mois donné.
 *
 * @ORM\Entity(repositoryClass=CraRepository::class)
 * @UniqueEntity(
 *     fields={"user", "mois"},
 *     errorPath="mois",
 *     message="Cet utilisateur a déjà soumis un CRA sur ce mois. Il faut modifier l'autre CRA plutôt."
 * )
 */
class Cra
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="cras")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="date")
     */
    private $mois;

    /**
     * Tableau contenant une liste de '1', '0' ou '0.5'
     * correspondant aux jours travaillés dans le mois.
     *
     * @ORM\Column(type="simple_array", nullable=true)
     *
     * @var float[]
     */
    private $jours = [];

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $modifiedAt;

    public function __construct()
    {
        $this->modifiedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getMois(): ?\DateTimeInterface
    {
        return $this->mois;
    }

    public function setMois(\DateTimeInterface $mois): self
    {
        $this->mois = $mois;

        return $this;
    }

    /**
     * @return float[]
     */
    public function getJours(): ?array
    {
        return array_map('floatval', $this->jours);
    }

    /**
     * @param float[] $jours
     *
     * @return self
     */
    public function setJours(?array $jours): self
    {
        $this->jours = $jours;

        return $this;
    }

    public function getModifiedAt(): ?\DateTimeInterface
    {
        return $this->modifiedAt;
    }

    public function setModifiedAt(\DateTimeInterface $modifiedAt): self
    {
        $this->modifiedAt = $modifiedAt;

        return $this;
    }
}

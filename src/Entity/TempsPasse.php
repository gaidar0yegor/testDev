<?php

namespace App\Entity;

use App\Repository\TempsPasseRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TempsPasseRepository::class)
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
     * @ORM\Column(type="integer", nullable=true)
     */
    private $pourcent_sur_le_mois;

    /**
     * @ORM\ManyToOne(targetEntity=Users::class, inversedBy="tempsPasses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $users;

    /**
     * @ORM\ManyToOne(targetEntity=Projets::class, inversedBy="temps_passe")
     * @ORM\JoinColumn(nullable=false)
     */
    private $projets;

    /**
     * @ORM\Column(type="date")
     */
    private $date_de_saisie;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPourcentSurLeMois(): ?int
    {
        return $this->pourcent_sur_le_mois;
    }

    public function setPourcentSurLeMois(?int $pourcent_sur_le_mois): self
    {
        $this->pourcent_sur_le_mois = $pourcent_sur_le_mois;

        return $this;
    }

    public function getUsers(): ?Users
    {
        return $this->users;
    }

    public function setUsers(?Users $users): self
    {
        $this->users = $users;

        return $this;
    }

    public function getProjets(): ?Projets
    {
        return $this->projets;
    }

    public function setProjets(?Projets $projets): self
    {
        $this->projets = $projets;

        return $this;
    }

    public function getDateDeSaisie(): ?\DateTimeInterface
    {
        return $this->date_de_saisie;
    }

    public function setDateDeSaisie(\DateTimeInterface $date_de_saisie): self
    {
        $this->date_de_saisie = $date_de_saisie;

        return $this;
    }
}

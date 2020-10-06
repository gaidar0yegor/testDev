<?php

namespace App\Entity;

use App\Repository\LicencesRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LicencesRepository::class)
 */
class Licences
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="binary")
     */
    private $statut;

    /**
     * @ORM\Column(type="date")
     */
    private $date_activation;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $date_desactivation;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $cle;

    /**
     * @ORM\ManyToOne(targetEntity=Societes::class, inversedBy="Licences")
     * @ORM\JoinColumn(nullable=false)
     */
    private $societes;

    /**
     * @ORM\OneToOne(targetEntity=Users::class, mappedBy="licences", cascade={"persist", "remove"})
     */
    private $users;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatut()
    {
        return $this->statut;
    }

    public function setStatut($statut): self
    {
        $this->statut = $statut;

        return $this;
    }

    public function getDateActivation(): ?\DateTimeInterface
    {
        return $this->date_activation;
    }

    public function setDateActivation(\DateTimeInterface $date_activation): self
    {
        $this->date_activation = $date_activation;

        return $this;
    }

    public function getDateDesactivation(): ?\DateTimeInterface
    {
        return $this->date_desactivation;
    }

    public function setDateDesactivation(?\DateTimeInterface $date_desactivation): self
    {
        $this->date_desactivation = $date_desactivation;

        return $this;
    }

    public function getCle(): ?string
    {
        return $this->cle;
    }

    public function setCle(string $cle): self
    {
        $this->cle = $cle;

        return $this;
    }

    public function getSocietes(): ?Societes
    {
        return $this->societes;
    }

    public function setSocietes(?Societes $societes): self
    {
        $this->societes = $societes;

        return $this;
    }

    public function getUsers(): ?Users
    {
        return $this->users;
    }

    public function setUsers(?Users $users): self
    {
        $this->users = $users;

        // set (or unset) the owning side of the relation if necessary
        $newLicences = null === $users ? null : $this;
        if ($users->getLicences() !== $newLicences) {
            $users->setLicences($newLicences);
        }

        return $this;
    }
}

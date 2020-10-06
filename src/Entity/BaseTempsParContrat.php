<?php

namespace App\Entity;

use App\Repository\BaseTempsParContratRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=BaseTempsParContratRepository::class)
 */
class BaseTempsParContrat
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
    private $libelle_contrat;

    /**
     * @ORM\Column(type="float")
     */
    private $cadre_nb_heures_mensuelles;

    /**
     * @ORM\Column(type="float")
     */
    private $non_cadre_nb_heures_mensuelles;

    /**
     * @ORM\OneToMany(targetEntity=Users::class, mappedBy="base_temps_par_contrat")
     */
    private $users;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelleContrat(): ?string
    {
        return $this->libelle_contrat;
    }

    public function setLibelleContrat(string $libelle_contrat): self
    {
        $this->libelle_contrat = $libelle_contrat;

        return $this;
    }

    public function getCadreNbHeuresMensuelles(): ?float
    {
        return $this->cadre_nb_heures_mensuelles;
    }

    public function setCadreNbHeuresMensuelles(float $cadre_nb_heures_mensuelles): self
    {
        $this->cadre_nb_heures_mensuelles = $cadre_nb_heures_mensuelles;

        return $this;
    }

    public function getNonCadreNbHeuresMensuelles(): ?float
    {
        return $this->non_cadre_nb_heures_mensuelles;
    }

    public function setNonCadreNbHeuresMensuelles(float $non_cadre_nb_heures_mensuelles): self
    {
        $this->non_cadre_nb_heures_mensuelles = $non_cadre_nb_heures_mensuelles;

        return $this;
    }

    /**
     * @return Collection|Users[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(Users $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setBaseTempsParContrat($this);
        }

        return $this;
    }

    public function removeUser(Users $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            // set the owning side to null (unless already changed)
            if ($user->getBaseTempsParContrat() === $this) {
                $user->setBaseTempsParContrat(null);
            }
        }

        return $this;
    }
}

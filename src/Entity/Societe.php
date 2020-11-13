<?php

namespace App\Entity;

use App\HasSocieteInterface;
use App\Repository\SocieteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SocieteRepository::class)
 */
class Societe implements HasSocieteInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=45)
     */
    private $raisonSociale;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $siret;

    /**
     * Heures travaillées par jours par défaut par les employés.
     *
     * @ORM\Column(type="decimal", precision=5, scale=3, nullable=true)
     */
    private $heuresParJours;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nb_licences;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nb_licences_dispo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $chemin_logo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nom_logo;

    /**
     * @ORM\OneToMany(targetEntity=Licences::class, mappedBy="societes")
     */
    private $Licences;

    /**
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="societe", orphanRemoval=true)
     */
    private $users;

    /**
     * @ORM\ManyToOne(targetEntity=SocieteStatut::class, inversedBy="societes")
     */
    private $statut;

    public function __construct()
    {
        $this->Licences = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRaisonSociale(): ?string
    {
        return $this->raisonSociale;
    }

    public function setRaisonSociale(string $raisonSociale): self
    {
        $this->raisonSociale = $raisonSociale;

        return $this;
    }

    public function getSiret(): ?string
    {
        return $this->siret;
    }

    public function setSiret(string $siret): self
    {
        $this->siret = $siret;

        return $this;
    }

    public function getHeuresParJours(): ?float
    {
        return $this->heuresParJours;
    }

    public function setHeuresParJours(float $heuresParJours): self
    {
        $this->heuresParJours = $heuresParJours;

        return $this;
    }

    public function getNbLicences(): ?int
    {
        return $this->nb_licences;
    }

    public function setNbLicences(int $nb_licences): self
    {
        $this->nb_licences = $nb_licences;

        return $this;
    }

    public function getNbLicencesDispo(): ?int
    {
        return $this->nb_licences_dispo;
    }

    public function setNbLicencesDispo(int $nb_licences_dispo): self
    {
        $this->nb_licences_dispo = $nb_licences_dispo;

        return $this;
    }

    public function getCheminLogo(): ?string
    {
        return $this->chemin_logo;
    }

    public function setCheminLogo(?string $chemin_logo): self
    {
        $this->chemin_logo = $chemin_logo;

        return $this;
    }

    public function getNomLogo(): ?string
    {
        return $this->nom_logo;
    }

    public function setNomLogo(?string $nom_logo): self
    {
        $this->nom_logo = $nom_logo;

        return $this;
    }

    /**
     * @return Collection|Licences[]
     */
    public function getLicences(): Collection
    {
        return $this->Licences;
    }

    public function addLicence(Licences $licence): self
    {
        if (!$this->Licences->contains($licence)) {
            $this->Licences[] = $licence;
            $licence->setSocietes($this);
        }

        return $this;
    }

    public function removeLicence(Licences $licence): self
    {
        if ($this->Licences->contains($licence)) {
            $this->Licences->removeElement($licence);
            // set the owning side to null (unless already changed)
            if ($licence->getSocietes() === $this) {
                $licence->setSocietes(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setSociete($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            // set the owning side to null (unless already changed)
            if ($user->getSociete() === $this) {
                $user->setSociete(null);
            }
        }

        return $this;
    }

    public function getStatut(): ?SocieteStatut
    {
        return $this->statut;
    }

    public function setStatut(?SocieteStatut $statut): self
    {
        $this->statut = $statut;

        return $this;
    }


    public function getSociete(): ?Societe
    {
        return $this;
    }

}

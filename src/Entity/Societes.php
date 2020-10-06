<?php

namespace App\Entity;

use App\Repository\SocietesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SocietesRepository::class)
 */
class Societes
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
    private $raison_sociale;

    /**
     * @ORM\Column(type="string", length=45)
     */
    private $siret;

    /**
     * @ORM\Column(type="integer")
     */
    private $nb_licences;

    /**
     * @ORM\Column(type="integer")
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
     * @ORM\OneToMany(targetEntity=Users::class, mappedBy="societes", orphanRemoval=true)
     */
    private $users;

    /**
     * @ORM\ManyToOne(targetEntity=StatutsSociete::class, inversedBy="societes")
     */
    private $statuts_societe;

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
        return $this->raison_sociale;
    }

    public function setRaisonSociale(string $raison_sociale): self
    {
        $this->raison_sociale = $raison_sociale;

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
            $user->setSocietes($this);
        }

        return $this;
    }

    public function removeUser(Users $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            // set the owning side to null (unless already changed)
            if ($user->getSocietes() === $this) {
                $user->setSocietes(null);
            }
        }

        return $this;
    }

    public function getStatutsSociete(): ?StatutsSociete
    {
        return $this->statuts_societe;
    }

    public function setStatutsSociete(?StatutsSociete $statuts_societe): self
    {
        $this->statuts_societe = $statuts_societe;

        return $this;
    }
}

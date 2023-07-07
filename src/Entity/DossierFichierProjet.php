<?php

namespace App\Entity;

use App\HasSocieteInterface;
use App\Repository\DossierFichierProjetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DossierFichierProjetRepository::class)
 */
class DossierFichierProjet implements HasSocieteInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nomMd5;

    /**
     * @ORM\Column(name="nom", type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity=FichierProjet::class, mappedBy="dossierFichierProjet", cascade={"remove"})
     */
    private $fichierProjets;

    /**
     * @ORM\ManyToOne(targetEntity=Projet::class, inversedBy="dossierFichierProjets")
     * @ORM\JoinColumn(nullable=false)
     */
    private $projet;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->fichierProjets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function setDefaultFolderName(): self
    {
        $this->setNomMd5(md5(uniqid()));

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection|FichierProjet[]
     */
    public function getFichierProjets(): Collection
    {
        return $this->fichierProjets;
    }

    /**
     * @return array|FichierProjet[]
     */
    public function getAccessibleFichierProjets(SocieteUser $societeUser): array
    {
        $fichierProjets = [];
        foreach ($this->fichierProjets as $fichierProjet){
            if (
                ( $societeUser->isAdminFo() || $fichierProjet->getSocieteUsers()->contains($societeUser) ) &&
                (
                    $fichierProjet->getFaitMarquant() === null ||
                    ($fichierProjet->getFaitMarquant() && $fichierProjet->getFaitMarquant()->getTrashedAt() === null)
                )
            ){
                $fichierProjets[] = $fichierProjet;
            }
        }
        return $fichierProjets;
    }

    public function addFichierProjet(FichierProjet $fichierProjet): self
    {
        if (!$this->fichierProjets->contains($fichierProjet)) {
            $this->fichierProjets[] = $fichierProjet;
            $fichierProjet->setDossierFichierProjet($this);
        }

        return $this;
    }

    public function removeFichierProjet(FichierProjet $fichierProjet): self
    {
        if ($this->fichierProjets->removeElement($fichierProjet)) {
            // set the owning side to null (unless already changed)
            if ($fichierProjet->getDossierFichierProjet() === $this) {
                $fichierProjet->setDossierFichierProjet(null);
            }
        }

        return $this;
    }

    public function getSociete(): ?Societe
    {
        return $this->getProjet()->getSociete();
    }

    public function getNomMd5(): ?string
    {
        return $this->nomMd5;
    }

    public function setNomMd5(string $nomMd5): self
    {
        $this->nomMd5 = $nomMd5;

        return $this;
    }

    public function getProjet(): Projet
    {
        return $this->projet;
    }

    public function setProjet(?Projet $projet): self
    {
        $this->projet = $projet;

        return $this;
    }
}

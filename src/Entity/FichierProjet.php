<?php

namespace App\Entity;

use App\HasSocieteInterface;
use App\ProjetResourceInterface;
use App\Repository\FichiersProjetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FichiersProjetRepository::class)
 */
class FichierProjet implements HasSocieteInterface, ProjetResourceInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Fichier::class, cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $fichier;

    /**
     * @ORM\ManyToOne(targetEntity=Projet::class, inversedBy="fichierProjets")
     * @ORM\JoinColumn(nullable=false)
     */
    private $projet;

    /**
     * @ORM\ManyToOne(targetEntity=SocieteUser::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $uploadedBy;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity=FaitMarquant::class, inversedBy="fichierProjets")
     */
    private $faitMarquant;

    /**
     * @ORM\ManyToMany(targetEntity=SocieteUser::class, inversedBy="fichierProjets")
     */
    private $societeUsers;

    /**
     * @ORM\Column(type="boolean", options={"default" : false})
     */
    private $isAccessibleParObservateurExterne;

    /**
     * @ORM\Column(type="json")
     */
    private $accessesChoices = [];

    /**
     * @ORM\ManyToOne(targetEntity=DossierFichierProjet::class, inversedBy="fichierProjets")
     */
    private $dossierFichierProjet;

    public function __construct()
    {
        $this->societeUsers = new ArrayCollection();
        $this->isAccessibleParObservateurExterne = false;
        $this->accessesChoices = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFichier(): ?Fichier
    {
        return $this->fichier;
    }

    public function setFichier(?Fichier $fichier): self
    {
        $this->fichier = $fichier;

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

    public function getUploadedBy(): ?SocieteUser
    {
        return $this->uploadedBy;
    }

    public function setUploadedBy(SocieteUser $uploadedBy): self
    {
        $this->uploadedBy = $uploadedBy;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getSociete(): ?Societe
    {
        return $this->getProjet()->getSociete();
    }

    public function getOwner(): SocieteUser
    {
        return $this->uploadedBy;
    }

    public function getFaitMarquant(): ?FaitMarquant
    {
        return $this->faitMarquant;
    }

    public function setFaitMarquant(?FaitMarquant $faitMarquant): self
    {
        $this->faitMarquant = $faitMarquant;

        return $this;
    }

    /**
     * @return Collection|SocieteUser[]
     */
    public function getSocieteUsers(): Collection
    {
        return $this->societeUsers;
    }

    public function addSocieteUser(SocieteUser $societeUser): self
    {
        if (!$this->societeUsers->contains($societeUser)) {
            $this->societeUsers[] = $societeUser;
        }

        return $this;
    }

    public function removeSocieteUser(SocieteUser $societeUser): self
    {
        $this->societeUsers->removeElement($societeUser);

        return $this;
    }

    public function getIsAccessibleParObservateurExterne(): ?bool
    {
        return $this->isAccessibleParObservateurExterne;
    }

    public function setIsAccessibleParObservateurExterne(bool $isAccessibleParObservateurExterne): self
    {
        $this->isAccessibleParObservateurExterne = $isAccessibleParObservateurExterne;

        return $this;
    }

    public function getAccessesChoices(): ?array
    {
        return $this->accessesChoices;
    }

    public function isPublic(): bool
    {
        return count($this->accessesChoices) === 0 || in_array('all',$this->accessesChoices);
    }

    public function setAccessesChoices(array $accessesChoices): self
    {
        $this->accessesChoices = $accessesChoices;

        return $this;
    }

    public function getDossierFichierProjet(): ?DossierFichierProjet
    {
        return $this->dossierFichierProjet;
    }

    public function setDossierFichierProjet(?DossierFichierProjet $dossierFichierProjet): self
    {
        $this->dossierFichierProjet = $dossierFichierProjet;

        return $this;
    }

    public function getRelativeProjetLocationPath(): string
    {
        return "{$this->getSociete()->getId()}/{$this->getProjet()->getId()}/";
    }

    public function getRelativeFileLocationPath(): string
    {
        return $this->getRelativeProjetLocationPath() . ($this->getDossierFichierProjet() ? "{$this->getDossierFichierProjet()->getNomMd5()}/" : "");
    }

    public function getRelativeFilePath(): string
    {
        return is_object($this->getFichier()) && $this->getFichier()->getNomMd5()
            ? $this->getRelativeFileLocationPath() . $this->getFichier()->getNomMd5()
            : "";
    }
}

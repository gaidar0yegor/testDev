<?php

namespace App\Entity;

use App\HasSocieteInterface;
use App\ProjetResourceInterface;
use App\Repository\FichiersProjetRepository;
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
     * @ORM\ManyToOne(targetEntity=User::class)
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

    public function getUploadedBy(): ?User
    {
        return $this->uploadedBy;
    }

    public function setUploadedBy(User $uploadedBy): self
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

    public function getOwner(): User
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
}

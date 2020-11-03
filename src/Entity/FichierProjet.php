<?php

namespace App\Entity;

use App\Repository\FichiersProjetRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FichiersProjetRepository::class)
 */
class FichierProjet
{

    /**
     * @ORM\Id()
     * @ORM\Column(type="string")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $chemin_fichier;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom_fichier;

    /**
     * @ORM\Column(type="string", length=45)
     */
    private $nom_uploader;

    /**
     * @ORM\ManyToOne(targetEntity=Projet::class, inversedBy="fichierProjets")
     * @ORM\JoinColumn(nullable=false)
     */
    private $projet;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCheminFichier(): ?string
    {
        return $this->chemin_fichier;
    }

    public function setCheminFichier(string $chemin_fichier): self
    {
        $this->chemin_fichier = $chemin_fichier;

        return $this;
    }

    public function getNomFichier()
    {
        return $this->nom_fichier;
    }

    public function setNomFichier($nom_fichier): self
    {
        $this->nom_fichier = $nom_fichier;

        return $this;
    }

    public function getNomUploader(): ?string
    {
        return $this->nom_uploader;
    }

    public function setNomUploader(string $nom_uploader): self
    {
        $this->nom_uploader = $nom_uploader;

        return $this;
    }

    public function getProjet(): ?Projet
    {
        return $this->projet;
    }

    public function setProjet(?Projet $projet): self
    {
        $this->projet = $projet;

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
}

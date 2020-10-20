<?php

namespace App\Entity;

use App\Repository\ProjetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity(repositoryClass=ProjetRepository::class)
 */
class Projet
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
    private $titre;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $resume;

    /**
     * @ORM\Column(type="date")
     */
    private $dateDebut;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateFin;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $statutRdi;

    /**
     * @ORM\Column(type="boolean")
     */
    private $projetCollaboratif;

    /**
     * @ORM\Column(type="boolean")
     */
    private $projetPpp;

    /**
     * @ORM\OneToMany(targetEntity=FichierProjet::class, mappedBy="projets", orphanRemoval=true)
     */
    private $fichierProjets;

    /**
     * @ORM\ManyToOne(targetEntity=StatutProjet::class, inversedBy="projets")
     * @ORM\JoinColumn(nullable=false)
     */
    private $statutProjet;

    /**
     * @ORM\OneToMany(targetEntity=ParticipantsProjet::class, mappedBy="projets", orphanRemoval=true)
     */
    private $participantsProjet;

    /**
     * @ORM\OneToMany(targetEntity=FaitsMarquants::class, mappedBy="projets", orphanRemoval=true)
     */
    private $faitsMarquants;

    /**
     * @ORM\OneToMany(targetEntity=TempsPasse::class, mappedBy="projets", orphanRemoval=true)
     */
    private $tempsPasse;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $acronyme;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $projetInterne;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $chefDeProjet;

    public function __construct()
    {
        $this->fichierProjets = new ArrayCollection();
        $this->participantsProjet = new ArrayCollection();
        $this->faitsMarquants = new ArrayCollection();
        $this->tempsPasse = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;

        return $this;
    }

    public function getResume(): ?string
    {
        return $this->resume;
    }

    public function setResume(?string $resume): self
    {
        $this->resume = $resume;

        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): self
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }
 
    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(?\DateTimeInterface $dateFin): self
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    public function getStatutRdi(): ?int
    {
        return $this->statutRdi;
    }

    public function setStatutRdi(int $statutRdi): self
    {
        $this->statutRdi = $statutRdi;

        return $this;
    }

    public function getProjetCollaboratif(): ?bool
    {
        return $this->projetCollaboratif;
    }

    public function setProjetCollaboratif(bool $projetCollaboratif): self
    {
        $this->projetCollaboratif = $projetCollaboratif;

        return $this;
    }

    public function getProjetPpp(): ?bool
    {
        return $this->projetPpp;
    }

    public function setProjetPpp(bool $projetPpp): self
    {
        $this->projetPpp = $projetPpp;

        return $this;
    }

    /**
     * @return Collection|FichierProjet[]
     */
    public function getFichierProjets(): Collection
    {
        return $this->fichierProjets;
    }

    public function addFichierProjet(FichierProjet $fichierProjet): self
    {
        if (!$this->fichierProjets->contains($fichierProjet)) {
            $this->fichierProjets[] = $fichierProjet;
            $fichierProjet->setProjet($this);
        }

        return $this;
    }

    public function removeFichierProjet(FichierProjet $fichierProjet): self
    {
        if ($this->fichierProjets->contains($fichierProjet)) {
            $this->fichierProjets->removeElement($fichierProjet);
            if ($fichierProjet->getProjet() === $this) {
                $fichierProjet->setProjet(null);
            }
        }

        return $this;
    }

    public function getStatutProjet(): ?StatutProjet
    {
        return $this->statutProjet;
    }

    public function setStatutProjet(?StatutProjet $statutProjet): self
    {
        $this->statutProjet = $statutProjet;

        return 
        $this;
    }

    /**
     * @return Collection|ParticipantsProjet[]
     */
    public function getParticipantsProjet(): Collection
    {
        return $this->participantsProjet;
    }

    public function addParticipantsProjet(ParticipantsProjet $participantsProjet): self
    {
        if (!$this->participantsProjet->contains($participantsProjet)) {
            $this->participantsProjet[] = $participantsProjet;
            $participantsProjet->setProjets($this);
        }

        return $this;
    }

    public function removeParticipantsProjet(ParticipantsProjet $participantsProjet): self
    {
        if ($this->participantsProjet->contains($participantsProjet)) {
            $this->participantsProjet->removeElement($participantsProjet);
            if ($participantsProjet->getProjets() === $this) {
                $participantsProjet->setProjets(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|FaitsMarquants[]
     */
    public function getFaitsMarquants(): Collection
    {
        return $this->faitsMarquants;
    }

    public function addFaitsMarquant(FaitsMarquants $faitsMarquant): self
    {
        if (!$this->faitsMarquants->contains($faitsMarquant)) {
            $this->faitsMarquants[] = $faitsMarquant;
            $faitsMarquant->setProjet($this);
        }

        return $this;
    }

    public function removeFaitsMarquant(FaitsMarquants $faitsMarquant): self
    {
        if ($this->faitsMarquants->contains($faitsMarquant)) {
            $this->faitsMarquants->removeElement($faitsMarquant);
            if ($faitsMarquant->getProjet() === $this) {
                $faitsMarquant->setProjet(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|TempsPasse[]
     */
    public function getTempsPasse(): Collection
    {
        return $this->tempsPasse;
    }

    public function addTempsPasse(TempsPasse $tempsPasse): self
    {
        if (!$this->tempsPasse->contains($tempsPasse)) {
            $this->tempsPasse[] = $tempsPasse;
            $tempsPasse->setProjet($this);
        }

        return $this;
    }

    public function removeTempsPasse(TempsPasse $tempsPasse): self
    {
        if ($this->tempsPasse->contains($tempsPasse)) {
            $this->tempsPasse->removeElement($tempsPasse);
            if ($tempsPasse->getProjet() === $this) {
                $tempsPasse->setProjet(null);
            }
        }

        return $this;
    }

    public function getAcronyme(): ?string
    {
        return $this->acronyme;
    }

    public function setAcronyme(string $acronyme): self
    {
        $this->acronyme = $acronyme;

        return $this;
    }

    public function getProjetInterne(): ?bool
    {
        return $this->projetInterne;
    }

    public function setProjetInterne(bool $projetInterne): self
    {
        $this->projetInterne = $projetInterne;

        return $this;
    }

    public function getChefDeProjet(): ?string
    {
        return $this->chefDeProjet;
    }

    public function setChefDeProjet(?string $chefDeProjet): self
    {
        $this->chefDeProjet = $chefDeProjet;

        return $this;
    }
}

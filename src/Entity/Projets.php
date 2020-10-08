<?php

namespace App\Entity;

use App\Repository\ProjetsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProjetsRepository::class)
 */
class Projets
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
    private $date_debut;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $date_fin;

    /**
     * @ORM\Column(type="integer")
     */
    private $statut_rdi;

    /**
     * @ORM\Column(type="boolean")
     */
    private $projet_collaboratif;

    /**
     * @ORM\Column(type="boolean")
     */
    private $projet_ppp;

    /**
     * @ORM\OneToMany(targetEntity=FichiersProjet::class, mappedBy="projets", orphanRemoval=true)
     */
    private $fichiersProjets;

    /**
     * @ORM\ManyToOne(targetEntity=StatutsProjet::class, inversedBy="projets")
     * @ORM\JoinColumn(nullable=false)
     */
    private $statuts_projet;

    /**
     * @ORM\OneToMany(targetEntity=ParticipantsProjet::class, mappedBy="projets", orphanRemoval=true)
     */
    private $participants_projet;

    /**
     * @ORM\OneToMany(targetEntity=FaitsMarquants::class, mappedBy="projets", orphanRemoval=true)
     */
    private $faits_marquants;

    /**
     * @ORM\OneToMany(targetEntity=TempsPasse::class, mappedBy="projets", orphanRemoval=true)
     */
    private $temps_passe;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $acronyme;

    public function __construct()
    {
        $this->fichiersProjets = new ArrayCollection();
        $this->participants_projet = new ArrayCollection();
        $this->faits_marquants = new ArrayCollection();
        $this->temps_passe = new ArrayCollection();
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
        return $this->date_debut;
    }

    public function setDateDebut(\DateTimeInterface $date_debut): self
    {
        $this->date_debut = $date_debut;

        return $this;
    }
 
    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->date_fin;
    }

    public function setDateFin(?\DateTimeInterface $date_fin): self
    {
        $this->date_fin = $date_fin;

        return $this;
    }

    public function getStatutRdi(): ?int
    {
        return $this->statut_rdi;
    }

    public function setStatutRdi(int $statut_rdi): self
    {
        $this->statut_rdi = $statut_rdi;

        return $this;
    }

    public function getProjetCollaboratif(): ?bool
    {
        return $this->projet_collaboratif;
    }

    public function setProjetCollaboratif(bool $projet_collaboratif): self
    {
        $this->projet_collaboratif = $projet_collaboratif;

        return $this;
    }

    public function getProjetPpp(): ?bool
    {
        return $this->projet_ppp;
    }

    public function setProjetPpp(bool $projet_ppp): self
    {
        $this->projet_ppp = $projet_ppp;

        return $this;
    }

    /**
     * @return Collection|FichiersProjet[]
     */
    public function getFichiersProjets(): Collection
    {
        return $this->fichiersProjets;
    }

    public function addFichiersProjet(FichiersProjet $fichiersProjet): self
    {
        if (!$this->fichiersProjets->contains($fichiersProjet)) {
            $this->fichiersProjets[] = $fichiersProjet;
            $fichiersProjet->setProjets($this);
        }

        return $this;
    }

    public function removeFichiersProjet(FichiersProjet $fichiersProjet): self
    {
        if ($this->fichiersProjets->contains($fichiersProjet)) {
            $this->fichiersProjets->removeElement($fichiersProjet);
            // set the owning side to null (unless already changed)
            if ($fichiersProjet->getProjets() === $this) {
                $fichiersProjet->setProjets(null);
            }
        }

        return $this;
    }

    public function getStatutsProjet(): ?StatutsProjet
    {
        return $this->statuts_projet;
    }

    public function setStatutsProjet(?StatutsProjet $statuts_projet): self
    {
        $this->statuts_projet = $statuts_projet;

        return $this;
    }

    /**
     * @return Collection|ParticipantsProjet[]
     */
    public function getParticipantsProjet(): Collection
    {
        return $this->participants_projet;
    }

    public function addParticipantsProjet(ParticipantsProjet $participantsProjet): self
    {
        if (!$this->participants_projet->contains($participantsProjet)) {
            $this->participants_projet[] = $participantsProjet;
            $participantsProjet->setProjets($this);
        }

        return $this;
    }

    public function removeParticipantsProjet(ParticipantsProjet $participantsProjet): self
    {
        if ($this->participants_projet->contains($participantsProjet)) {
            $this->participants_projet->removeElement($participantsProjet);
            // set the owning side to null (unless already changed)
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
        return $this->faits_marquants;
    }

    public function addFaitsMarquant(FaitsMarquants $faitsMarquant): self
    {
        if (!$this->faits_marquants->contains($faitsMarquant)) {
            $this->faits_marquants[] = $faitsMarquant;
            $faitsMarquant->setProjets($this);
        }

        return $this;
    }

    public function removeFaitsMarquant(FaitsMarquants $faitsMarquant): self
    {
        if ($this->faits_marquants->contains($faitsMarquant)) {
            $this->faits_marquants->removeElement($faitsMarquant);
            // set the owning side to null (unless already changed)
            if ($faitsMarquant->getProjets() === $this) {
                $faitsMarquant->setProjets(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|TempsPasse[]
     */
    public function getTempsPasse(): Collection
    {
        return $this->temps_passe;
    }

    public function addTempsPasse(TempsPasse $tempsPasse): self
    {
        if (!$this->temps_passe->contains($tempsPasse)) {
            $this->temps_passe[] = $tempsPasse;
            $tempsPasse->setProjets($this);
        }

        return $this;
    }

    public function removeTempsPasse(TempsPasse $tempsPasse): self
    {
        if ($this->temps_passe->contains($tempsPasse)) {
            $this->temps_passe->removeElement($tempsPasse);
            // set the owning side to null (unless already changed)
            if ($tempsPasse->getProjets() === $this) {
                $tempsPasse->setProjets(null);
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
}

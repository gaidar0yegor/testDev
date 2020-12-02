<?php

namespace App\Entity;

use App\Exception\RdiException;
use App\HasSocieteInterface;
use App\Repository\ProjetRepository;
use App\Role;
use App\Validator as AppAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity(repositoryClass=ProjetRepository::class)
 */
class Projet implements HasSocieteInterface
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
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateDebut;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateFin;

    /**
     * @ORM\Column(type="boolean")
     */
    private $projetCollaboratif;

    /**
     * @ORM\Column(type="boolean")
     */
    private $projetPpp;

    /**
     * @ORM\OneToMany(targetEntity=FichierProjet::class, mappedBy="projet", orphanRemoval=true)
     */
    private $fichierProjets;

    /**
     * @ORM\OneToMany(targetEntity=ProjetParticipant::class, mappedBy="projet", orphanRemoval=true, cascade={"persist"})
     *
     * @Assert\Valid
     * @AppAssert\ExactlyOneChefDeProjet
     * @AppAssert\AllSameSociete
     */
    private $projetParticipants;

    /**
     * @ORM\OneToMany(targetEntity=FaitMarquant::class, mappedBy="projet", orphanRemoval=true)
     * @ORM\OrderBy({"date" = "DESC"})
     */
    private $faitMarquants;

    /**
     * @ORM\OneToMany(targetEntity=TempsPasse::class, mappedBy="projet", orphanRemoval=true)
     */
    private $tempsPasses;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $acronyme;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $projetInterne;

    public function __construct()
    {
        $this->fichierProjets = new ArrayCollection();
        $this->projetParticipants = new ArrayCollection();
        $this->faitMarquants = new ArrayCollection();
        $this->tempsPasses = new ArrayCollection();
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

    public function setDateDebut(?\DateTimeInterface $dateDebut): self
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

    /**
     * @param \DateTimeInterface $datetime Jour à évaluer
     *
     * @return bool Si au moment $datetime, le projet est actif (entre date début et fin, inclus)
     */
    public function isProjetActiveInDate(\DateTimeInterface $datetime): bool
    {
        if (null !== $this->dateDebut && $datetime < $this->dateDebut) {
            return false;
        }

        if (null !== $this->dateFin && $datetime > $this->dateFin) {
            return false;
        }

        return true;
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

    public function getStatut(): string
    {
        $now = new \DateTime();

        if ($now < $this->dateDebut) {
            return 'À venir';
        }

        if ($now > $this->dateFin) {
            return 'Terminé';
        }

        return 'En cours';
    }

    /**
     * @return Collection|ProjetParticipant[]
     */
    public function getProjetParticipants(): Collection
    {
        return $this->projetParticipants;
    }

    public function addProjetParticipant(ProjetParticipant $projetParticipant): self
    {
        if (!$this->projetParticipants->contains($projetParticipant)) {
            $this->projetParticipants[] = $projetParticipant;
            $projetParticipant->setProjet($this);
        }

        return $this;
    }

    public function removeProjetParticipant(ProjetParticipant $projetParticipant): self
    {
        if ($this->projetParticipants->contains($projetParticipant)) {
            $this->projetParticipants->removeElement($projetParticipant);
            if ($projetParticipant->getProjet() === $this) {
                $projetParticipant->setProjet(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|FaitMarquant[]
     */
    public function getFaitMarquants(): Collection
    {
        return $this->faitMarquants;
    }

    public function hasFaitMarquants(): bool
    {
        return count($this->faitMarquants) > 0;
    }

    public function addFaitMarquant(FaitMarquant $faitMarquant): self
    {
        if (!$this->faitMarquants->contains($faitMarquant)) {
            $this->faitMarquants[] = $faitMarquant;
            $faitMarquant->setProjet($this);
        }

        return $this;
    }

    public function removeFaitMarquant(FaitMarquant $faitMarquant): self
    {
        if ($this->faitMarquants->contains($faitMarquant)) {
            $this->faitMarquants->removeElement($faitMarquant);
            if ($faitMarquant->getProjet() === $this) {
                $faitMarquant->setProjet(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|TempsPasse[]
     */
    public function getTempsPasses(): Collection
    {
        return $this->tempsPasses;
    }

    public function addTempsPasse(TempsPasse $tempsPasse): self
    {
        if (!$this->tempsPasses->contains($tempsPasse)) {
            $this->tempsPasses[] = $tempsPasse;
            $tempsPasse->setProjet($this);
        }

        return $this;
    }

    public function removeTempsPasse(TempsPasse $tempsPasse): self
    {
        if ($this->tempsPasses->contains($tempsPasse)) {
            $this->tempsPasses->removeElement($tempsPasse);
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

    public function getChefDeProjet(): ?User
    {
        foreach ($this->projetParticipants as $participant) {
            if ($participant->getRole() === Role::CDP) {
                return $participant->getUser();
            }
        }

        throw new RdiException('This projet has no Chef de Projet');
    }

    public function getSociete(): ?Societe
    {
        return $this->getChefDeProjet()->getSociete();
    }

    public function isRdi(): bool
    {
        return $this->projetPpp;
    }

    /**
     * @Assert\Callback
     */
    public function validateDateDebutFin(ExecutionContextInterface $context, $payload)
    {
        if (null === $this->dateDebut || null === $this->dateFin) {
            return;
        }

        if ($this->dateFin < $this->dateDebut) {
            $context
                ->buildViolation('La date de fin doit être égale ou après la date de début.')
                ->atPath('dateFin')
                ->addViolation()
            ;
        }
    }
}

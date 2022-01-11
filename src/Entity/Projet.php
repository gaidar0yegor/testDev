<?php

namespace App\Entity;

use App\Exception\RdiException;
use App\HasSocieteInterface;
use App\Repository\ProjetRepository;
use App\Security\Role\RoleProjet;
use App\Validator as AppAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ProjetRepository::class)
 * @AppAssert\DatesOrdered(
 *      start="dateDebut",
 *      end="dateFin"
 * )
 */
class Projet implements HasSocieteInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     *
     * @Serializer\Groups({"recentProjets", "saisieTemps","lastActivities"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Serializer\Groups({"Default", "saisieTemps"})
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
     * @ORM\ManyToOne(targetEntity=Societe::class, inversedBy="projets")
     * @ORM\JoinColumn(nullable=false)
     */
    private $societe;

    /**
     * @ORM\Column(type="boolean")
     */
    private $projetCollaboratif;

    /**
     * @ORM\Column(type="boolean")
     */
    private $projetPpp;

    /**
     * @ORM\OneToMany(targetEntity=FichierProjet::class, mappedBy="projet", orphanRemoval=true, cascade={"persist"})
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
     * @ORM\OneToMany(targetEntity=ProjetObservateurExterne::class, mappedBy="projet", orphanRemoval=true, cascade={"persist"})
     *
     * @Assert\Valid
     */
    private $projetObservateurExternes;

    /**
     * @ORM\OneToMany(targetEntity=FaitMarquant::class, mappedBy="projet", orphanRemoval=true)
     * @ORM\OrderBy({"date" = "DESC", "id" = "DESC"})
     */
    private $faitMarquants;

    /**
     * @ORM\OneToMany(targetEntity=TempsPasse::class, mappedBy="projet", orphanRemoval=true)
     */
    private $tempsPasses;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Serializer\Groups({"recentProjets", "saisieTemps","lastActivities"})
     */
    private $acronyme;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $projetInterne;

    /**
     * @ORM\OneToMany(targetEntity=ProjetActivity::class, mappedBy="projet", orphanRemoval=true)
     */
    private $projetActivities;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $rdiScore;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $rdiScoreReliability;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity=ProjetUrl::class, mappedBy="projet", orphanRemoval=true, cascade={"persist"})
     */
    private $projetUrls;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $colorCode;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isSuspended;

    /**
     * @ORM\OneToMany(targetEntity=ProjetSuspendPeriod::class, mappedBy="projet", orphanRemoval=true, cascade={"persist"})
     */
    private $projetSuspendPeriods;

    /**
     * @ORM\OneToMany(targetEntity=DossierFichierProjet::class, mappedBy="projet", orphanRemoval=true, cascade={"persist"})
     */
    private $dossierFichierProjets;

    public function __construct()
    {
        $this->fichierProjets = new ArrayCollection();
        $this->projetParticipants = new ArrayCollection();
        $this->projetObservateurExternes = new ArrayCollection();
        $this->faitMarquants = new ArrayCollection();
        $this->tempsPasses = new ArrayCollection();
        $this->projetCollaboratif = false;
        $this->projetPpp = false;
        $this->projetInterne = false;
        $this->projetActivities = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->projetUrls = new ArrayCollection();
        $this->colorCode = '#e9ece6';
        $this->projetSuspendPeriods = new ArrayCollection();
        $this->dossierFichierProjets = new ArrayCollection();
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

    public function getSociete(): ?Societe
    {
        return $this->societe;
    }

    public function setSociete(?Societe $societe): self
    {
        $this->societe = $societe;

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

    /**
     * @return array|FichierProjet[]
     */
    public function getAccessibleExterneFichierProjets(): array
    {
        $fichierProjets = [];
        foreach ($this->fichierProjets as $fichierProjet){
            if (
                $fichierProjet->getIsAccessibleParObservateurExterne() &&
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

        if ($this->isSuspended) {
            return 'Suspendu';
        }

        if (null !== $this->dateDebut && $now < $this->dateDebut) {
            return 'À venir';
        }

        if (null !== $this->dateFin && $now > $this->dateFin) {
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

    /**
     * @return Collection|ProjetParticipant[]
     */
    public function getActiveProjetParticipants(): Collection
    {
        return $this->projetParticipants->filter(function (ProjetParticipant $projetParticipant) {
            return $projetParticipant->getSocieteUser()->getStatut() === SocieteUser::STATUT_ACTIVE;
        });
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
     * @return Collection|ProjetObservateurExterne[]
     */
    public function getProjetObservateurExternes(): Collection
    {
        return $this->projetObservateurExternes;
    }

    /**
     * @return Collection|ProjetObservateurExterne[]
     */
    public function getActiveProjetObservateurExternes(): Collection
    {
        return $this->projetObservateurExternes->filter(function (ProjetObservateurExterne $projetObservateurExterne) {
            return $projetObservateurExterne->getUser()->getEnabled();
        });
    }

    public function addProjetObservateurExterne(ProjetObservateurExterne $projetObservateurExterne): self
    {
        if (!$this->projetObservateurExternes->contains($projetObservateurExterne)) {
            $this->projetObservateurExternes[] = $projetObservateurExterne;
            $projetObservateurExterne->setProjet($this);
        }

        return $this;
    }

    public function removeProjetObservateurExterne(ProjetObservateurExterne $projetObservateurExterne): self
    {
        if ($this->projetObservateurExternes->contains($projetObservateurExterne)) {
            $this->projetObservateurExternes->removeElement($projetObservateurExterne);
            if ($projetObservateurExterne->getProjet() === $this) {
                $projetObservateurExterne->setProjet(null);
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

    public function getChefDeProjet(): SocieteUser
    {
        foreach ($this->projetParticipants as $participant) {
            if ($participant->getRole() === RoleProjet::CDP) {
                return $participant->getSocieteUser();
            }
        }

        throw new RdiException('This projet has no Chef de Projet');
    }

    public function getContributeurs(): array
    {
        $contributeurs = [];

        foreach ($this->projetParticipants as $participant) {
            if ($participant->getRole() === RoleProjet::CONTRIBUTEUR) {
                array_push($contributeurs,$participant);
            }
        }

        return $contributeurs;
    }

    public function getObservateurs(): array
    {
        $observatuers = [];

        foreach ($this->projetParticipants as $participant) {
            if ($participant->getRole() === RoleProjet::OBSERVATEUR) {
                array_push($observatuers,$participant);
            }
        }

        return $observatuers;
    }

    public function isRdi(): bool
    {
        return $this->projetPpp;
    }

    /**
     * @return Collection|ProjetActivity[]
     */
    public function getProjetActivities(): Collection
    {
        return $this->projetActivities;
    }

    public function addProjetActivity(ProjetActivity $projetActivity): self
    {
        if (!$this->projetActivities->contains($projetActivity)) {
            $this->projetActivities[] = $projetActivity;
            $projetActivity->setProjet($this);
        }

        return $this;
    }

    public function removeProjetActivity(ProjetActivity $projetActivity): self
    {
        if ($this->projetActivities->removeElement($projetActivity)) {
            // set the owning side to null (unless already changed)
            if ($projetActivity->getProjet() === $this) {
                $projetActivity->setProjet(null);
            }
        }

        return $this;
    }

    public function getRdiScore(): ?float
    {
        return $this->rdiScore;
    }

    public function setRdiScore(?float $rdiScore): self
    {
        $this->rdiScore = $rdiScore;

        return $this;
    }

    public function getRdiScoreReliability(): ?float
    {
        return $this->rdiScoreReliability;
    }

    public function setRdiScoreReliability(?float $rdiScoreReliability): self
    {
        $this->rdiScoreReliability = $rdiScoreReliability;

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
     * @return Collection|ProjetUrl[]
     */
    public function getProjetUrls(): Collection
    {
        return $this->projetUrls;
    }

    public function addProjetUrl(ProjetUrl $projetUrl): self
    {
        if (!$this->projetUrls->contains($projetUrl)) {
            $this->projetUrls[] = $projetUrl;
            $projetUrl->setProjet($this);
        }

        return $this;
    }

    public function removeProjetUrl(ProjetUrl $projetUrl): self
    {
        if ($this->projetUrls->removeElement($projetUrl)) {
            // set the owning side to null (unless already changed)
            if ($projetUrl->getProjet() === $this) {
                $projetUrl->setProjet(null);
            }
        }

        return $this;
    }

    public function getColorCode(): ?string
    {
        return $this->colorCode;
    }

    public function setColorCode(string $colorCode): self
    {
        $this->colorCode = $colorCode;

        return $this;
    }

    public function getIsSuspended(): ?bool
    {
        return $this->isSuspended;
    }

    public function setIsSuspended(?bool $isSuspended): self
    {
        $this->isSuspended = $isSuspended;

        return $this;
    }

    /**
     * @return Collection|ProjetSuspendPeriod[]
     */
    public function getProjetSuspendPeriods(): Collection
    {
        return $this->projetSuspendPeriods;
    }

    public function addProjetSuspendPeriod(ProjetSuspendPeriod $projetSuspendPeriod): self
    {
        if (!$this->projetSuspendPeriods->contains($projetSuspendPeriod)) {
            $this->projetSuspendPeriods[] = $projetSuspendPeriod;
            $projetSuspendPeriod->setProjet($this);
        }

        return $this;
    }

    public function removeProjetSuspendPeriod(ProjetSuspendPeriod $projetSuspendPeriod): self
    {
        if ($this->projetSuspendPeriods->removeElement($projetSuspendPeriod)) {
            // set the owning side to null (unless already changed)
            if ($projetSuspendPeriod->getProjet() === $this) {
                $projetSuspendPeriod->setProjet(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|DossierFichierProjet[]
     */
    public function getDossierFichierProjets(): Collection
    {
        return $this->dossierFichierProjets;
    }

    public function addDossierFichierProjet(DossierFichierProjet $dossierFichierProjet): self
    {
        if (!$this->dossierFichierProjets->contains($dossierFichierProjet)) {
            $this->dossierFichierProjets[] = $dossierFichierProjet;
            $dossierFichierProjet->setProjet($this);
        }

        return $this;
    }

    public function removeDossierFichierProjet(DossierFichierProjet $dossierFichierProjet): self
    {
        if ($this->dossierFichierProjets->removeElement($dossierFichierProjet)) {
            // set the owning side to null (unless already changed)
            if ($dossierFichierProjet->getProjet() === $this) {
                $dossierFichierProjet->setProjet(null);
            }
        }

        return $this;
    }
}

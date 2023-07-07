<?php

namespace App\Entity;

use App\HasSocieteInterface;
use Symfony\Component\Validator\Constraints as Assert;
use App\ProjetResourceInterface;
use App\Repository\FaitMarquantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FaitMarquantRepository::class)
 */
class FaitMarquant implements ProjetResourceInterface, HasSocieteInterface
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
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * Date du fait marquant
     * @Assert\Range(
     *      max = "+1 hours"
     * )
     *
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $geolocalisation;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=SocieteUser::class, inversedBy="faitMarquants")
     * @ORM\JoinColumn(nullable=false)
     */
    private $createdBy;

    /**
     * @ORM\ManyToOne(targetEntity=Projet::class, inversedBy="faitMarquants")
     * @ORM\JoinColumn(nullable=false)
     */
    private $projet;

    /**
     * @ORM\OneToMany(targetEntity=FichierProjet::class, mappedBy="faitMarquant", cascade={"all"})
     */
    private $fichierProjets;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $trashedAt;

    /**
     * @ORM\ManyToOne(targetEntity=SocieteUser::class)
     */
    private $trashedBy;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $sendedToEmails = [];

    /**
     * @ORM\ManyToOne(targetEntity=ProjetPlanningTask::class, inversedBy="faitMarquants")
     */
    private $projetPlanningTask;

    /**
     * @ORM\OneToMany(targetEntity=FaitMarquantComment::class, mappedBy="faitMarquant", orphanRemoval=true)
     *
     * @ORM\OrderBy({"createdAt" = "ASC", "id" = "ASC"})
     */
    private $comments;

    public function __construct()
    {
        $this->fichierProjets = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->comments = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

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

    public function getCreatedBy(): ?SocieteUser
    {
        return $this->createdBy;
    }

    public function setCreatedBy(SocieteUser $createdBy): self
    {
        $this->createdBy = $createdBy;

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

    public function getOwner(): SocieteUser
    {
        return $this->createdBy;
    }

    /**
     * @return Collection|FichierProjet[]
     */
    public function getFichierProjets(): Collection
    {
        return $this->fichierProjets;
    }

    public function setFichierProjets($fichierProjets): self
    {
        $this->fichierProjets = $fichierProjets;

        foreach ($fichierProjets as $fichierProjet) {
            $fichierProjet->setFaitMarquant($this);
        }

        return $this;
    }

    public function addFichierProjet(FichierProjet $fichierProjet): self
    {
        if (!$this->fichierProjets->contains($fichierProjet)) {
            $this->fichierProjets[] = $fichierProjet;
            $fichierProjet->setFaitMarquant($this);
        }

        return $this;
    }

    public function removeFichierProjet(FichierProjet $fichierProjet): self
    {
        if ($this->fichierProjets->contains($fichierProjet)) {
            $this->fichierProjets->removeElement($fichierProjet);
            // set the owning side to null (unless already changed)
            if ($fichierProjet->getFaitMarquant() === $this) {
                $fichierProjet->setFaitMarquant(null);
            }
        }

        return $this;
    }

    public function getSociete(): ?Societe
    {
        return $this->projet->getSociete();
    }

    public function getTrashedAt(): ?\DateTimeInterface
    {
        return $this->trashedAt;
    }

    public function setTrashedAt(?\DateTimeInterface $trashedAt): self
    {
        $this->trashedAt = $trashedAt;

        return $this;
    }

    public function getTrashedBy(): ?SocieteUser
    {
        return $this->trashedBy;
    }

    public function setTrashedBy(?SocieteUser $trashedBy): self
    {
        $this->trashedBy = $trashedBy;

        return $this;
    }

    public function getSendedToEmails(): ?array
    {
        return $this->sendedToEmails;
    }

    public function setSendedToEmails(?array $sendedToEmails): self
    {
        $this->sendedToEmails = $sendedToEmails;

        return $this;
    }

    public function getProjetPlanningTask(): ?ProjetPlanningTask
    {
        return $this->projetPlanningTask;
    }

    public function setProjetPlanningTask(?ProjetPlanningTask $projetPlanningTask): self
    {
        $this->projetPlanningTask = $projetPlanningTask;

        return $this;
    }

    /**
     * @return Collection|FaitMarquantComment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(FaitMarquantComment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setFaitMarquant($this);
        }

        return $this;
    }

    public function removeComment(FaitMarquantComment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getFaitMarquant() === $this) {
                $comment->setFaitMarquant(null);
            }
        }

        return $this;
    }

    public function getGeolocalisation(): ?string
    {
        return $this->geolocalisation;
    }

    public function setGeolocalisation(?string $geolocalisation): self
    {
        $this->geolocalisation = $geolocalisation;

        return $this;
    }
}

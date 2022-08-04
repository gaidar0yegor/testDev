<?php

namespace App\Entity\LabApp;

use App\Entity\Fichier;
use App\EtudeResourceInterface;
use App\HasUserBookInterface;
use App\Repository\LabApp\EtudeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=EtudeRepository::class)
 */
class Etude implements HasUserBookInterface, EtudeResourceInterface
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
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $acronyme;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateDebut;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateFin;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $resume;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity=Note::class, mappedBy="etude", orphanRemoval=true)
     * @ORM\OrderBy({"date" = "DESC", "id" = "DESC"})
     */
    private $notes;

    /**
     * @ORM\ManyToOne(targetEntity=UserBook::class, inversedBy="etudes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $userBook;

    /**
     * @ORM\OneToMany(targetEntity=FichierEtude::class, mappedBy="etude", orphanRemoval=true, cascade={"persist"})
     */
    private $fichierEtudes;

    /**
     * @ORM\OneToOne(targetEntity=Fichier::class, cascade={"persist", "remove"})
     */
    private $banner;

    /**
     * @ORM\ManyToOne(targetEntity=Equipe::class, inversedBy="etudes")
     *
     * @Assert\Valid
     */
    private $equipe;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->notes = new ArrayCollection();
        $this->fichierEtudes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

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

    public function getResume(): ?string
    {
        return $this->resume;
    }

    public function setResume(?string $resume): self
    {
        $this->resume = $resume;

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
     * @return Collection|Note[]
     */
    public function getNotes(): Collection
    {
        return $this->notes;
    }

    public function addNote(Note $note): self
    {
        if (!$this->notes->contains($note)) {
            $this->notes[] = $note;
            $note->setEtude($this);
        }

        return $this;
    }

    public function removeNote(Note $note): self
    {
        if ($this->notes->removeElement($note)) {
            // set the owning side to null (unless already changed)
            if ($note->getEtude() === $this) {
                $note->setEtude(null);
            }
        }

        return $this;
    }

    public function getStatut(): string
    {
        $now = new \DateTime();

        if (null !== $this->dateDebut && $now < $this->dateDebut) {
            return 'upcoming';
        }

        if (null !== $this->dateFin && $now > $this->dateFin) {
            return 'ended';
        }

        return 'in_progress';
    }

    public function getOwner(): UserBook
    {
        return $this->userBook;
    }

    public function getUserBook(): ?UserBook
    {
        return $this->userBook;
    }

    public function setUserBook(?UserBook $userBook): self
    {
        $this->userBook = $userBook;

        return $this;
    }

    public function getEtude(): Etude
    {
        return $this;
    }

    /**
     * @return Collection|FichierEtude[]
     */
    public function getFichierEtudes(): Collection
    {
        return $this->fichierEtudes;
    }

    public function addFichierEtude(FichierEtude $fichierEtude): self
    {
        if (!$this->fichierEtudes->contains($fichierEtude)) {
            $this->fichierEtudes[] = $fichierEtude;
            $fichierEtude->setEtude($this);
        }

        return $this;
    }

    public function removeFichierEtude(FichierEtude $fichierEtude): self
    {
        if ($this->fichierEtudes->removeElement($fichierEtude)) {
            // set the owning side to null (unless already changed)
            if ($fichierEtude->getEtude() === $this) {
                $fichierEtude->setEtude(null);
            }
        }

        return $this;
    }

    public function getBanner(): ?Fichier
    {
        return $this->banner;
    }

    public function setBanner(?Fichier $banner): self
    {
        $this->banner = $banner;

        return $this;
    }

    public function getEquipe(): ?Equipe
    {
        return $this->equipe;
    }

    public function setEquipe(?Equipe $equipe): self
    {
        $this->equipe = $equipe;

        return $this;
    }
}

<?php

namespace App\Entity\LabApp;

use App\EtudeResourceInterface;
use App\HasUserBookInterface;
use App\Repository\LabApp\NoteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=NoteRepository::class)
 */
class Note implements HasUserBookInterface, EtudeResourceInterface
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
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $readingName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $author;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $reference;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=Etude::class, inversedBy="notes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $etude;

    /**
     * @ORM\ManyToOne(targetEntity=UserBook::class, inversedBy="notes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $createdBy;

    /**
     * @ORM\OneToMany(targetEntity=FichierEtude::class, mappedBy="note", cascade={"all"})
     */
    private $fichierEtudes;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getReadingName(): ?string
    {
        return $this->readingName;
    }

    public function setReadingName(?string $readingName): self
    {
        $this->readingName = $readingName;

        return $this;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(?string $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function isReadingNote(): bool
    {
        return $this->readingName !== null || $this->author !== null || $this->reference !== null;
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

    public function getEtude(): Etude
    {
        return $this->etude;
    }

    public function setEtude(?Etude $etude): self
    {
        $this->etude = $etude;

        return $this;
    }

    public function getCreatedBy(): ?UserBook
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?UserBook $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getOwner(): UserBook
    {
        return $this->createdBy;
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
            $fichierEtude->setNote($this);
        }

        return $this;
    }

    public function removeFichierEtude(FichierEtude $fichierEtude): self
    {
        if ($this->fichierEtudes->removeElement($fichierEtude)) {
            // set the owning side to null (unless already changed)
            if ($fichierEtude->getNote() === $this) {
                $fichierEtude->setNote(null);
            }
        }

        return $this;
    }

    public function getUserBook(): ?UserBook
    {
        return $this->etude->getUserBook();
    }
}

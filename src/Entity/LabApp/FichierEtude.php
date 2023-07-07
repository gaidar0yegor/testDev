<?php

namespace App\Entity\LabApp;

use App\Entity\Fichier;
use App\EtudeResourceInterface;
use App\HasUserBookInterface;
use App\Repository\LabApp\FichierEtudeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FichierEtudeRepository::class)
 */
class FichierEtude implements HasUserBookInterface, EtudeResourceInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Fichier::class, cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $fichier;

    /**
     * @ORM\ManyToOne(targetEntity=Etude::class, inversedBy="fichierEtudes", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $etude;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity=Note::class, inversedBy="fichierEtudes")
     */
    private $note;

    /**
     * @ORM\ManyToOne(targetEntity=UserBook::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $uploadedBy;

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

    public function getEtude(): Etude
    {
        return $this->etude;
    }

    public function setEtude(?Etude $etude): self
    {
        $this->etude = $etude;

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

    public function getNote(): ?Note
    {
        return $this->note;
    }

    public function setNote(?Note $note): self
    {
        $this->note = $note;

        return $this;
    }

    public function getOwner(): UserBook
    {
        return $this->uploadedBy;
    }

    public function getUserBook(): ?UserBook
    {
        return $this->getEtude()->getUserBook();
    }

    public function getRelativeEtudeLocationPath(): string
    {
        return "{$this->getUserBook()->getId()}/{$this->getEtude()->getId()}/";
    }

    public function getRelativeFileLocationPath(): string
    {
        return $this->getRelativeEtudeLocationPath();
    }

    public function getRelativeFilePath(): string
    {
        return is_object($this->getFichier()) && $this->getFichier()->getNomMd5()
            ? $this->getRelativeFileLocationPath() . $this->getFichier()->getNomMd5()
            : "";
    }

    public function getUploadedBy(): ?UserBook
    {
        return $this->uploadedBy;
    }

    public function setUploadedBy(?UserBook $uploadedBy): self
    {
        $this->uploadedBy = $uploadedBy;

        return $this;
    }
}

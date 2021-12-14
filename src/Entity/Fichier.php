<?php

namespace App\Entity;

use App\Repository\FichierRepository;
use Doctrine\ORM\Mapping as ORM;
use Serializable;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @ORM\Entity(repositoryClass=FichierRepository::class)
 */
class Fichier implements Serializable
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
    private $nomMd5;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nomFichier;

    /**
     * @ORM\Column(type="date")
     */
    private $dateUpload;

    /**
     * @var UploadedFile
     */
    private $file;

    public function __construct()
    {
        $this->dateUpload = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomMd5(): ?string
    {
        return $this->nomMd5;
    }

    public function setNomMd5(string $nomMd5): self
    {
        $this->nomMd5 = $nomMd5;

        return $this;
    }

    public function getNomFichier()
    {
        return $this->nomFichier;
    }

    public function setNomFichier($nomFichier): self
    {
        $this->nomFichier = $nomFichier;

        return $this;
    }

    public function getDateUpload(): ?\DateTimeInterface
    {
        return $this->dateUpload;
    }

    public function setDateUpload(\DateTimeInterface $dateUpload): self
    {
        $this->dateUpload = $dateUpload;

        return $this;
    }

    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    public function setFile(?UploadedFile $file): self
    {
        $this->file = $file;

        return $this;
    }

    public function setDefaultFilename(): self
    {
        $fileName = md5(uniqid()).'.'.$this->file->guessExtension();
        $nomFichier = null === $this->nomFichier ? $this->file->getClientOriginalName() : $this->nomFichier.'.'.$this->file->guessExtension();
        $this
            ->setNomFichier($nomFichier)
            ->setNomMd5($fileName)
        ;

        return $this;
    }

    public function serialize()
    {
        return serialize([
            $this->id,
            $this->nomMd5,
            $this->nomFichier,
            $this->dateUpload,
        ]);
    }

    public function unserialize($data)
    {
        list(
            $this->id,
            $this->nomMd5,
            $this->nomFichier,
            $this->dateUpload,
        ) = unserialize($data);
    }
}

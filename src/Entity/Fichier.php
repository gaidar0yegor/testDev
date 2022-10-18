<?php

namespace App\Entity;

use App\Repository\FichierRepository;
use Doctrine\ORM\Mapping as ORM;
use Serializable;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Validator as AppAssert;

/**
 * @ORM\Entity(repositoryClass=FichierRepository::class)
 */
class Fichier implements Serializable
{
    /**
     * Max file size en byte
     *
     * @var string
     */
    const MAX_FILE_SIZE = 5242880;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Groups({"organigramme","comment"})
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

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $externalLink;

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
        $extension = null !== $this->file ? '.' . $this->file->guessExtension() : '';
        $fileName = md5(uniqid()) . $extension;

        if (null === $this->nomFichier){
            if (null !== $this->file){
                $nomFichier = $this->file->getClientOriginalName();
            } else {
                $nomFichier = $fileName;
            }
        } else {
            $nomFichier =  $this->nomFichier . $extension;
        }

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

    public function getExternalLink(): ?string
    {
        return $this->externalLink;
    }

    public function setExternalLink(?string $externalLink): self
    {
        $this->externalLink = $externalLink;

        return $this;
    }
}

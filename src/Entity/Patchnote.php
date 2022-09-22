<?php

namespace App\Entity;

use App\Repository\PatchnoteRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=PatchnoteRepository::class)
 */
class Patchnote
{
    const CORP_APP = "corp_app";
    const LAB_APP = "lab_app";

    const RDI_APPS = [
        self::CORP_APP,
        self::LAB_APP
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     */
    private $date;

    /**
     * @ORM\Column(type="text")
     */
    private $notes;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $version;

    /**
     * @Assert\Choice(choices=Patchnote::RDI_APPS, message="Choose a valid RDI application.")
     *
     * @ORM\Column(type="string", length=255)
     */
    private $rdiApp;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isDraft;

    public function __construct()
    {
        $this->date = new \DateTime();
        $this->isDraft = true;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(string $notes): self
    {
        $this->notes = $notes;

        return $this;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(string $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function getRdiApp(): ?string
    {
        return $this->rdiApp;
    }

    public function setRdiApp(string $rdiApp): self
    {
        $this->rdiApp = $rdiApp;

        return $this;
    }

    public function getIsDraft(): ?bool
    {
        return $this->isDraft;
    }

    public function setIsDraft(bool $isDraft): self
    {
        $this->isDraft = $isDraft;

        return $this;
    }
}

<?php

namespace App\Entity;

use App\Repository\FaitMarquantCommentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=FaitMarquantCommentRepository::class)
 */
class FaitMarquantComment
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @Groups({"comment"})
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     *
     * @Groups({"comment"})
     */
    private $text;

    /**
     * @ORM\Column(type="datetime")
     *
     * @Groups({"comment"})
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=ProjetObservateurExterne::class)
     */
    private $observateurExterne;

    /**
     * @ORM\ManyToOne(targetEntity=SocieteUser::class)
     */
    private $societeUser;

    /**
     * @ORM\ManyToOne(targetEntity=FaitMarquant::class, inversedBy="comments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $faitMarquant;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

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

    public function getObservateurExterne(): ?ProjetObservateurExterne
    {
        return $this->observateurExterne;
    }

    public function setObservateurExterne(?ProjetObservateurExterne $observateurExterne): self
    {
        $this->observateurExterne = $observateurExterne;

        return $this;
    }

    public function getSocieteUser(): ?SocieteUser
    {
        return $this->societeUser;
    }

    public function setSocieteUser(?SocieteUser $societeUser): self
    {
        $this->societeUser = $societeUser;

        return $this;
    }

    /**
     * @return mixed
     *
     * @Groups({"comment"})
     */
    public function getCreatedBy()
    {
        return $this->observateurExterne !== null ? $this->observateurExterne : $this->societeUser;
    }

    /**
     * @return string
     *
     * @Groups({"comment"})
     */
    public function getCreatedByRole()
    {
        return $this->societeUser !== null ? $this->societeUser->getRole() : 'PROJET_OBSERVATEUR_EXTERNE';
    }

    public function getFaitMarquant(): ?FaitMarquant
    {
        return $this->faitMarquant;
    }

    public function setFaitMarquant(?FaitMarquant $faitMarquant): self
    {
        $this->faitMarquant = $faitMarquant;

        return $this;
    }


}

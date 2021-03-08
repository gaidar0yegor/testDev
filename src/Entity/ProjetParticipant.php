<?php

namespace App\Entity;

use App\HasSocieteInterface;
use App\Repository\ProjetParticipantRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=ProjetParticipantRepository::class)
 * @UniqueEntity(
 *     fields={"user", "projet"},
 *     errorPath="user",
 *     message="Cet utilisateur a déjà un rôle sur ce projet."
 * )
 */
class ProjetParticipant implements HasSocieteInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     */
    private $dateAjout;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="projetParticipants")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Projet::class, inversedBy="projetParticipants")
     * @ORM\JoinColumn(nullable=false)
     */
    private $projet;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $role;

    /**
     * The datetime of the last action $user did on $projet (view, update...)
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastActionAt;

    public function __construct()
    {
        $this->dateAjout = new \DateTime();
    }

    public static function create(User $user, Projet $projet, string $role): self
    {
        return (new self())
            ->setUser($user)
            ->setProjet($projet)
            ->setRole($role)
        ;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateAjout(): ?\DateTimeInterface
    {
        return $this->dateAjout;
    }

    public function setDateAjout(\DateTimeInterface $dateAjout): self
    {
        $this->dateAjout = $dateAjout;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getProjet(): ?Projet
    {
        return $this->projet;
    }

    public function setProjet(?Projet $projet): self
    {
        $this->projet = $projet;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getSociete(): ?Societe
    {
        if (null === $this->user) {
            return null;
        }

        return $this->user->getSociete();
    }

    public function getLastActionAt(): ?\DateTimeInterface
    {
        return $this->lastActionAt;
    }

    public function setLastActionAt(?\DateTimeInterface $lastActionAt): self
    {
        $this->lastActionAt = $lastActionAt;

        return $this;
    }

    public function setLastActionAtNow(): self
    {
        $this->lastActionAt = new \DateTime();

        return $this;
    }
}

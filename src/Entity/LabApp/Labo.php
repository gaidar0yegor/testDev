<?php

namespace App\Entity\LabApp;

use App\Entity\Fichier;
use App\Entity\User;
use App\Repository\LabApp\LaboRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity(repositoryClass=LaboRepository::class)
 */
class Labo
{
    /**
     * This Labo has been created by someone who followed
     * the inscription tunnel
     *
     * @param string
     */
    public const CREATED_FROM_INSCRIPTION = 'INSCRIPTION';

    /**
     * This Labo has been created by a back office user.
     *
     * @param string
     */
    public const CREATED_FROM_BACK_OFFICE = 'BACK_OFFICE';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $rnsr;

    /**
     * @ORM\Column(type="uuid")
     */
    private $uuid;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="string", length=31, nullable=true)
     */
    private $createdFrom;

    /**
     * @ORM\OneToOne(targetEntity=Fichier::class, cascade={"persist", "remove"})
     */
    private $logo;

    /**
     * @ORM\OneToMany(targetEntity=UserBook::class, mappedBy="labo", orphanRemoval=true, cascade={"persist"})
     */
    private $userBooks;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $createdBy;

    /**
     * @ORM\OneToMany(targetEntity=UserBookInvite::class, mappedBy="labo")
     */
    private $userBookInvites;

    /**
     * @ORM\OneToMany(targetEntity=Equipe::class, mappedBy="labo", orphanRemoval=true)
     */
    private $equipes;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
        $this->createdAt = new \DateTime();
        $this->userBooks = new ArrayCollection();
        $this->userBookInvites = new ArrayCollection();
        $this->equipes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRnsr(): ?string
    {
        return $this->rnsr;
    }

    public function setRnsr(?string $rnsr): self
    {
        $this->rnsr = $rnsr;

        return $this;
    }

    public function getUuid()
    {
        return $this->uuid;
    }

    public function setUuid($uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

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

    public function getCreatedFrom(): ?string
    {
        return $this->createdFrom;
    }

    public function setCreatedFrom(?string $createdFrom): self
    {
        $this->createdFrom = $createdFrom;

        return $this;
    }

    public function getLogo(): ?Fichier
    {
        return $this->logo;
    }

    public function setLogo(?Fichier $logo): self
    {
        $this->logo = $logo;

        return $this;
    }

    /**
     * @return Collection|UserBook[]
     */
    public function getUserBooks(): Collection
    {
        return $this->userBooks;
    }

    public function addUserBook(UserBook $userBook): self
    {
        if (!$this->userBooks->contains($userBook)) {
            $this->userBooks[] = $userBook;
            $userBook->setLabo($this);
        }

        return $this;
    }

    public function removeUserBook(UserBook $userBook): self
    {
        if ($this->userBooks->removeElement($userBook)) {
            // set the owning side to null (unless already changed)
            if ($userBook->getLabo() === $this) {
                $userBook->setLabo(null);
            }
        }

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * @return Collection|UserBookInvite[]
     */
    public function getUserBookInvites(): Collection
    {
        return $this->userBookInvites;
    }

    public function addUserBookInvitation(UserBookInvite $userBookInvitation): self
    {
        if (!$this->userBookInvites->contains($userBookInvitation)) {
            $this->userBookInvites[] = $userBookInvitation;
            $userBookInvitation->setLabo($this);
        }

        return $this;
    }

    public function removeUserBookInvitation(UserBookInvite $userBookInvitation): self
    {
        if ($this->userBookInvites->removeElement($userBookInvitation)) {
            // set the owning side to null (unless already changed)
            if ($userBookInvitation->getLabo() === $this) {
                $userBookInvitation->setLabo(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Equipe[]
     */
    public function getEquipes(): Collection
    {
        return $this->equipes;
    }

    public function addEquipe(Equipe $equipe): self
    {
        if (!$this->equipes->contains($equipe)) {
            $this->equipes[] = $equipe;
            $equipe->setLabo($this);
        }

        return $this;
    }

    public function removeEquipe(Equipe $equipe): self
    {
        if ($this->equipes->removeElement($equipe)) {
            // set the owning side to null (unless already changed)
            if ($equipe->getLabo() === $this) {
                $equipe->setLabo(null);
            }
        }

        return $this;
    }
}

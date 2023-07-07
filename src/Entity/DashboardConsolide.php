<?php

namespace App\Entity;

use App\Repository\DashboardConsolideRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DashboardConsolideRepository::class)
 */
class DashboardConsolide
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="dashboardConsolides")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToMany(targetEntity=SocieteUser::class, inversedBy="dashboardConsolides")
     */
    private $societeUsers;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    public function __construct()
    {
        $this->societeUsers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @return Collection|SocieteUser[]
     */
    public function getSocieteUsers(): Collection
    {
        return $this->societeUsers;
    }

    public function addSocieteUser(SocieteUser $societeUser): self
    {
        if (!$this->societeUsers->contains($societeUser)) {
            $this->societeUsers[] = $societeUser;
        }

        return $this;
    }

    public function removeSocieteUser(SocieteUser $societeUser): self
    {
        $this->societeUsers->removeElement($societeUser);

        return $this;
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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}

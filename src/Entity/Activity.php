<?php

namespace App\Entity;

use App\Repository\ActivityRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DoctrineExtensions\Query\Mysql\Date;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass=ActivityRepository::class)
 */
class Activity
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $datetime;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $type;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $parameters = [];

    /**
     * @ORM\OneToMany(targetEntity=SocieteUserNotification::class, mappedBy="activity", orphanRemoval=true)
     */
    private $societeUserNotifications;

    /**
     * @ORM\OneToMany(targetEntity=ProjetActivity::class, mappedBy="activity", orphanRemoval=true)
     *
     * @Serializer\Groups({"lastActivities"})
     */
    private $projetActivities;

    /**
     * @ORM\OneToMany(targetEntity=SocieteUserActivity::class, mappedBy="activity", orphanRemoval=true)
     */
    private $societeUserActivities;

    /**
     * @ORM\OneToMany(targetEntity=BoUserNotification::class, mappedBy="activity", orphanRemoval=true)
     */
    private $boUserNotifications;

    public function __construct()
    {
        $this->datetime = new DateTime();
        $this->societeUserNotifications = new ArrayCollection();
        $this->projetActivities = new ArrayCollection();
        $this->societeUserActivities = new ArrayCollection();
        $this->boUserNotifications = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDatetime(): ?\DateTimeInterface
    {
        return $this->datetime;
    }

    public function setDatetime(\DateTimeInterface $datetime): self
    {
        $this->datetime = $datetime;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getParameters(): ?array
    {
        return $this->parameters;
    }

    public function setParameters(?array $parameters): self
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * @return Collection|SocieteUserNotification[]
     */
    public function getSocieteUserNotifications(): Collection
    {
        return $this->societeUserNotifications;
    }

    public function addSocieteUserNotification(SocieteUserNotification $userNotification): self
    {
        if (!$this->societeUserNotifications->contains($userNotification)) {
            $this->societeUserNotifications[] = $userNotification;
            $userNotification->setActivity($this);
        }

        return $this;
    }

    public function removeSocieteUserNotification(SocieteUserNotification $userNotification): self
    {
        if ($this->societeUserNotifications->removeElement($userNotification)) {
            // set the owning side to null (unless already changed)
            if ($userNotification->getActivity() === $this) {
                $userNotification->setActivity(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ProjetActivity[]
     */
    public function getProjetActivities(): Collection
    {
        return $this->projetActivities;
    }

    public function addProjetActivity(ProjetActivity $projetActivity): self
    {
        if (!$this->projetActivities->contains($projetActivity)) {
            $this->projetActivities[] = $projetActivity;
            $projetActivity->setActivity($this);
        }

        return $this;
    }

    public function removeProjetActivity(ProjetActivity $projetActivity): self
    {
        if ($this->projetActivities->removeElement($projetActivity)) {
            // set the owning side to null (unless already changed)
            if ($projetActivity->getActivity() === $this) {
                $projetActivity->setActivity(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|SocieteUserActivity[]
     */
    public function getUserActivities(): Collection
    {
        return $this->societeUserActivities;
    }

    public function addSocieteUserActivity(SocieteUserActivity $societeUserActivity): self
    {
        if (!$this->societeUserActivities->contains($societeUserActivity)) {
            $this->societeUserActivities[] = $societeUserActivity;
            $societeUserActivity->setActivity($this);
        }

        return $this;
    }

    public function removeSocieteUserActivity(SocieteUserActivity $societeUserActivity): self
    {
        if ($this->societeUserActivities->removeElement($societeUserActivity)) {
            // set the owning side to null (unless already changed)
            if ($societeUserActivity->getActivity() === $this) {
                $societeUserActivity->setActivity(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|BoUserNotification[]
     */
    public function getBoUserNotifications(): Collection
    {
        return $this->boUserNotifications;
    }

    public function addBoUserNotification(BoUserNotification $boUserNotification): self
    {
        if (!$this->boUserNotifications->contains($boUserNotification)) {
            $this->boUserNotifications[] = $boUserNotification;
            $boUserNotification->setActivity($this);
        }

        return $this;
    }

    public function removeBoUserNotification(BoUserNotification $boUserNotification): self
    {
        if ($this->boUserNotifications->removeElement($boUserNotification)) {
            // set the owning side to null (unless already changed)
            if ($boUserNotification->getActivity() === $this) {
                $boUserNotification->setActivity(null);
            }
        }

        return $this;
    }
}

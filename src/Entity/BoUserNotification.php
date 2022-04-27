<?php

namespace App\Entity;

use App\Repository\BoUserNotificationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=BoUserNotificationRepository::class)
 */
class BoUserNotification
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="boUserNotifications")
     * @ORM\JoinColumn(nullable=false)
     */
    private $boUser;

    /**
     * @ORM\ManyToOne(targetEntity=Activity::class, inversedBy="boUserNotifications")
     * @ORM\JoinColumn(nullable=false)
     */
    private $activity;

    /**
     * @ORM\Column(type="boolean", options={"default" : false})
     */
    private $acknowledged;

    public function __construct()
    {
        $this->acknowledged = false;
    }

    public static function create(Activity $activity, User $user): self
    {
        return (new self())
            ->setActivity($activity)
            ->setBoUser($user);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBoUser(): ?User
    {
        return $this->boUser;
    }

    public function setBoUser(?User $boUser): self
    {
        $this->boUser = $boUser;

        return $this;
    }

    public function getActivity(): ?Activity
    {
        return $this->activity;
    }

    public function setActivity(?Activity $activity): self
    {
        $this->activity = $activity;

        return $this;
    }

    public function getAcknowledged(): ?bool
    {
        return $this->acknowledged;
    }

    public function setAcknowledged(bool $acknowledged): self
    {
        $this->acknowledged = $acknowledged;

        return $this;
    }
}

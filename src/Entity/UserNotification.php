<?php

namespace App\Entity;

use App\Repository\UserNotificationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Ignore;

/**
 * Type d'activité qui sera affiché en tant que notification
 * importante, sous la cloche, et qui sera en highlight
 * tant que l'user ne l'a pas encore lue.
 *
 * @ORM\Entity(repositoryClass=UserNotificationRepository::class)
 */
class UserNotification
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="userNotifications")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Ignore
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Activity::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $activity;

    /**
     * @ORM\Column(type="boolean")
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
            ->setUser($user)
        ;
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

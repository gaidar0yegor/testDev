<?php

namespace App\Entity;

use App\Repository\SocieteUserNotificationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Ignore;

/**
 * Type d'activité qui sera affiché en tant que notification
 * importante, sous la cloche, et qui sera en highlight
 * tant que l'user ne l'a pas encore lue.
 *
 * @ORM\Entity(repositoryClass=SocieteUserNotificationRepository::class)
 */
class SocieteUserNotification
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=SocieteUser::class, inversedBy="societeUserNotifications")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Ignore
     */
    private $societeUser;

    /**
     * @ORM\ManyToOne(targetEntity=Activity::class, inversedBy="societeUserNotifications")
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

    public static function create(Activity $activity, SocieteUser $societeUser): self
    {
        return (new self())
            ->setActivity($activity)
            ->setSocieteUser($societeUser)
        ;
    }

    public function getId(): ?int
    {
        return $this->id;
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

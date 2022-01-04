<?php

namespace App\Entity;

use App\Repository\SocieteUserPeriodRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Validator as AppAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=SocieteUserPeriodRepository::class)
 *
 *  @AppAssert\DatesOrdered(
 *     start="dateEntry",
 *     end="dateLeave"
 * )
 */
class SocieteUserPeriod
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateEntry;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateLeave;

    /**
     * @ORM\ManyToOne(targetEntity=SocieteUser::class, inversedBy="societeUserPeriods")
     * @ORM\JoinColumn(nullable=false)
     */
    private $societeUser;

    public static function create(\DateTime $dateEntry): self
    {
        return (new self())
            ->setDateEntry($dateEntry)
            ;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateEntry(): ?\DateTimeInterface
    {
        return $this->dateEntry;
    }

    public function setDateEntry(?\DateTimeInterface $dateEntry): self
    {
        $this->dateEntry = $dateEntry;

        return $this;
    }

    public function getDateLeave(): ?\DateTimeInterface
    {
        return $this->dateLeave;
    }

    public function setDateLeave(?\DateTimeInterface $dateLeave): self
    {
        $this->dateLeave = $dateLeave;

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
}

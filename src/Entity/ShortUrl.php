<?php

namespace App\Entity;

use App\Repository\ShortUrlRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ShortUrlRepository::class)
 */
class ShortUrl
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $originalUrl;

    /**
     * @ORM\Column(type="string", length=127, unique=true)
     */
    private $token;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * Date of last re-utilisation of this same token
     * for another short url creation.
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $reusedAt;

    /**
     * @ORM\Column(type="integer")
     */
    private $clicked;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastClickedAt;

    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->clicked = 0;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOriginalUrl(): ?string
    {
        return $this->originalUrl;
    }

    public function setOriginalUrl(string $originalUrl): self
    {
        $this->originalUrl = $originalUrl;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

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

    public function getClicked(): ?int
    {
        return $this->clicked;
    }

    public function getLastClickedAt(): ?\DateTimeInterface
    {
        return $this->lastClickedAt;
    }

    public function click(): self
    {
        ++$this->clicked;
        $this->lastClickedAt = new DateTime();

        return $this;
    }

    public function getReusedAt(): ?\DateTimeInterface
    {
        return $this->reusedAt;
    }

    public function setReusedAt(?\DateTimeInterface $reusedAt): self
    {
        $this->reusedAt = $reusedAt;

        return $this;
    }
}

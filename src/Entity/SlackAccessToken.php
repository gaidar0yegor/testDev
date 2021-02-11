<?php

namespace App\Entity;

use App\HasSocieteInterface;
use App\Repository\SlackAccessTokenRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SlackAccessTokenRepository::class)
 */
class SlackAccessToken implements HasSocieteInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * Example: U01LQC6PE31
     *
     * @ORM\Column(type="string", length=15, nullable=true)
     */
    private $authedUserId;

    /**
     * Example: [channels:join, chat:write, incoming-webhook, chat:write.public]
     *
     * @ORM\Column(type="simple_array")
     */
    private $scope = [];

    /**
     * Example: xoxb-171939xxx-1718xxx-Chac3FVxxx
     *
     * @ORM\Column(type="string", length=255)
     */
    private $accessToken;

    /**
     * Example: U01M4KS4YLE
     *
     * @ORM\Column(type="string", length=15, nullable=true)
     */
    private $botUserId;

    /**
     * Example: T01M5BJDGUB
     *
     * @ORM\Column(type="string", length=15, nullable=true)
     */
    private $teamId;

    /**
     * Example: Afup
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $teamName;

    /**
     * Example: null
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $enterprise;

    /**
     * Example: false
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isEnterpriseInstall;

    /**
     * Example: #général
     *
     * @ORM\Column(type="string", length=255)
     */
    private $incomingWebhookChannel;

    /**
     * Example: C01MHQ37F4H
     *
     * @ORM\Column(type="string", length=15, nullable=true)
     */
    private $incomingWebhookChannelId;

    /**
     * Example: https://afup.slack.com/services/B01MC4TTDPV
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $incomingWebhookConfigurationUrl;

    /**
     * Example: https://hooks.slack.com/services/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
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $incomingWebhookUrl;

    /**
     * @ORM\ManyToOne(targetEntity=Societe::class, inversedBy="slackAccessTokens")
     * @ORM\JoinColumn(nullable=false)
     */
    private $societe;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $lastRequestSuccess;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lastRequestResponse;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastRequestSentAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthedUserId(): ?string
    {
        return $this->authedUserId;
    }

    public function setAuthedUserId(?string $authedUserId): self
    {
        $this->authedUserId = $authedUserId;

        return $this;
    }

    public function getScope(): ?array
    {
        return $this->scope;
    }

    public function setScope(array $scope): self
    {
        $this->scope = $scope;

        return $this;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken(string $accessToken): self
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    public function getBotUserId(): ?string
    {
        return $this->botUserId;
    }

    public function setBotUserId(?string $botUserId): self
    {
        $this->botUserId = $botUserId;

        return $this;
    }

    public function getTeamId(): ?string
    {
        return $this->teamId;
    }

    public function setTeamId(?string $teamId): self
    {
        $this->teamId = $teamId;

        return $this;
    }

    public function getTeamName(): ?string
    {
        return $this->teamName;
    }

    public function setTeamName(?string $teamName): self
    {
        $this->teamName = $teamName;

        return $this;
    }

    public function getEnterprise(): ?bool
    {
        return $this->enterprise;
    }

    public function setEnterprise(?bool $enterprise): self
    {
        $this->enterprise = $enterprise;

        return $this;
    }

    public function getIsEnterpriseInstall(): ?bool
    {
        return $this->isEnterpriseInstall;
    }

    public function setIsEnterpriseInstall(?bool $isEnterpriseInstall): self
    {
        $this->isEnterpriseInstall = $isEnterpriseInstall;

        return $this;
    }

    public function getIncomingWebhookChannel(): ?string
    {
        return $this->incomingWebhookChannel;
    }

    public function setIncomingWebhookChannel(string $incomingWebhookChannel): self
    {
        $this->incomingWebhookChannel = $incomingWebhookChannel;

        return $this;
    }

    public function getIncomingWebhookChannelId(): ?string
    {
        return $this->incomingWebhookChannelId;
    }

    public function setIncomingWebhookChannelId(?string $incomingWebhookChannelId): self
    {
        $this->incomingWebhookChannelId = $incomingWebhookChannelId;

        return $this;
    }

    public function getIncomingWebhookConfigurationUrl(): ?string
    {
        return $this->incomingWebhookConfigurationUrl;
    }

    public function setIncomingWebhookConfigurationUrl(?string $incomingWebhookConfigurationUrl): self
    {
        $this->incomingWebhookConfigurationUrl = $incomingWebhookConfigurationUrl;

        return $this;
    }

    public function getIncomingWebhookUrl(): ?string
    {
        return $this->incomingWebhookUrl;
    }

    public function setIncomingWebhookUrl(?string $incomingWebhookUrl): self
    {
        $this->incomingWebhookUrl = $incomingWebhookUrl;

        return $this;
    }

    public function getSociete(): ?Societe
    {
        return $this->societe;
    }

    public function setSociete(?Societe $societe): self
    {
        $this->societe = $societe;

        return $this;
    }

    public function getLastRequestSuccess(): ?bool
    {
        return $this->lastRequestSuccess;
    }

    public function setLastRequestSuccess(?bool $lastRequestSuccess): self
    {
        $this->lastRequestSuccess = $lastRequestSuccess;

        return $this;
    }

    public function getLastRequestResponse(): ?string
    {
        return $this->lastRequestResponse;
    }

    public function setLastRequestResponse(?string $lastRequestResponse): self
    {
        $this->lastRequestResponse = $lastRequestResponse;

        return $this;
    }

    public function getLastRequestSentAt(): ?\DateTimeInterface
    {
        return $this->lastRequestSentAt;
    }

    public function setLastRequestSentAt(?\DateTimeInterface $lastRequestSentAt): self
    {
        $this->lastRequestSentAt = $lastRequestSentAt;

        return $this;
    }
}

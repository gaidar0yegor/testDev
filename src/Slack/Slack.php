<?php

namespace App\Slack;

use App\Entity\SlackAccessToken;
use App\Entity\Societe;
use Doctrine\ORM\EntityManagerInterface;
use JoliCode\Slack\Api\Client;
use JoliCode\Slack\Api\Model\OauthV2AccessGetResponse200;
use JoliCode\Slack\ClientFactory;
use JoliCode\Slack\Exception\SlackErrorResponse;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Slack
{
    private string $slackClientId;

    private string $slackClientSecret;

    private EntityManagerInterface $em;

    private UrlGeneratorInterface $urlGenerator;

    public function __construct(
        string $slackClientId,
        string $slackClientSecret,
        EntityManagerInterface $em,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->slackClientId = $slackClientId;
        $this->slackClientSecret = $slackClientSecret;
        $this->em = $em;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * Call Slack OAuth enpoint to retreive an access token from a code.
     */
    public function requestAccessToken(string $code): SlackAccessToken
    {
        $client = ClientFactory::create('');

        $response = $client->oauthV2Access([
            'code' => $code,
            'client_id' => $this->slackClientId,
            'client_secret' => $this->slackClientSecret,
            'redirect_uri' => $this->generateRedirectUri(),
        ]);

        return self::responseToSlackAccessToken($response);
    }

    /**
     * Returns whether a slack access token has a direct message as channel.
     */
    public function isDirectMessage(SlackAccessToken $slackAccessToken): bool
    {
        $channelId = $slackAccessToken->getIncomingWebhookChannelId();

        return 'D' === substr($channelId, 0, 1);
    }

    /**
     * Returns the RDI-Manager endpoint that handles Slack redirect uri.
     */
    public function generateRedirectUri(): string
    {
        return $this->urlGenerator->generate('corp_app_slack', [], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * Call the $callback for each societe access token.
     * The callback takes as argument the slack Client and a SlackAccessToken instance.
     * Must return a SlackRequestResult.
     */
    public function callForSociete(Societe $societe, callable $callback): void
    {
        foreach ($societe->getSlackAccessTokens() as $slackAccessToken) {
            $client = ClientFactory::create($slackAccessToken->getAccessToken());

            $result = $callback($client, $slackAccessToken);

            if ($result instanceof SlackRequestResult) {
                $slackAccessToken->setLastRequestSuccess($result->isSuccess());
                $slackAccessToken->setLastRequestResponse($result->getMessage());
                $slackAccessToken->setLastRequestSentAt($result->getSentAt());
            }
        }

        $this->em->flush();
    }

    /**
     * Sends a simple message to all connected Slack channel of $societe.
     * If not connected to Slack, nothing will be sent.
     */
    public function sendMessage(Societe $societe, string $message, LoggerInterface $logger = null): void
    {
        if (null === $logger) {
            $logger = new NullLogger();
        }

        $this->callForSociete(
            $societe,
            function (Client $client, SlackAccessToken $slackAccessToken) use ($message, $logger) {
                $logger->info('Posting to channel '.$slackAccessToken->getIncomingWebhookChannel().' ...');

                try {
                    $client->chatPostMessage([
                        'channel' => $slackAccessToken->getIncomingWebhookChannelId(),
                        'text' => $message,
                    ]);

                    $logger->info('OK');

                    return new SlackRequestResult(true, 'Notification envoyée avec succès.');
                } catch (SlackErrorResponse $e) {
                    $logger->info('ERROR: '.$e->getMessage());

                    return new SlackRequestResult(false, $e->getMessage());
                }
            }
        );
    }

    /**
     * Sends an rich message to all connected Slack channel of $societe.
     * If not connected to Slack, nothing will be sent.
     *
     * See https://app.slack.com/block-kit-builder to build blocks.
     */
    public function sendBlocks(Societe $societe, array $blocks): void
    {
        $this->callForSociete(
            $societe,
            function (Client $client, SlackAccessToken $slackAccessToken) use ($blocks) {
                try {
                    $client->chatPostMessage([
                        'channel' => $slackAccessToken->getIncomingWebhookChannelId(),
                        'blocks' => json_encode($blocks),
                    ]);

                    return new SlackRequestResult(true, 'Notification envoyée avec succès.');
                } catch (SlackErrorResponse $e) {
                    return new SlackRequestResult(false, $e->getMessage());
                }
            }
        );
    }

    public static function responseToSlackAccessToken(OauthV2AccessGetResponse200 $response): SlackAccessToken
    {
        $slackAccessToken = new SlackAccessToken();

        $slackAccessToken
            ->setAuthedUserId($response['authed_user']['id'])
            ->setScope(explode(',', $response['scope']))
            ->setAccessToken($response['access_token'])
            ->setBotUserId($response['bot_user_id'])
            ->setTeamId($response['team']['id'])
            ->setTeamName($response['team']['name'])
            ->setEnterprise($response['enterprise'])
            ->setIsEnterpriseInstall($response['is_enterprise_install'])
            ->setIncomingWebhookChannel($response['incoming_webhook']['channel'])
            ->setIncomingWebhookChannelId($response['incoming_webhook']['channel_id'])
            ->setIncomingWebhookConfigurationUrl($response['incoming_webhook']['configuration_url'])
            ->setIncomingWebhookUrl($response['incoming_webhook']['url'])
        ;

        return $slackAccessToken;
    }
}

<?php

namespace App\Slack;

use App\Notification\Event\PlanningTaskNotCompletedNotification;
use App\Notification\Event\RappelSaisieTempsNotification;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class NotificationListener implements EventSubscriberInterface
{
    private Slack $slack;

    private UrlGeneratorInterface $urlGenerator;

    public function __construct(Slack $slack, UrlGeneratorInterface $urlGenerator)
    {
        $this->slack = $slack;
        $this->urlGenerator = $urlGenerator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RappelSaisieTempsNotification::class => 'rappelSaisieTemps',
            PlanningTaskNotCompletedNotification::class => 'planningTaskNotCompleted',
        ];
    }

    public function rappelSaisieTemps(RappelSaisieTempsNotification $event): void
    {
        $buttonLink = $this->urlGenerator->generate('app_fo_temps', [], UrlGeneratorInterface::ABSOLUTE_URL);

        $blocks = [
            [
                'type' => 'section',
                'text' => [
                    'type' => 'mrkdwn',
                    'text' => 'Bonjour ! Pourriez vous *saisir vos temps* sur RDI-Manager ?',
                ],
            ],
            [
                'type' => 'actions',
                'elements' => [
                    [
                        'type' => 'button',
                        'style' => 'primary',
                        'text' => [
                            'type' => 'plain_text',
                            'text' => 'Saisir mes temps :timer_clock:',
                            'emoji' => true,
                        ],
                        'value' => 'btn_saisie_temps',
                        'url' => $buttonLink,
                    ],
                ],
            ],
            [
                'type' => 'context',
                'elements' => [
                    [
                        'type' => 'plain_text',
                        'text' => 'Vous aurez juste à saisir les pourcentages de temps passés sur vos projets ce mois-ci :slightly_smiling_face:. Ce suivi de temps permettra ensuite de générer les feuilles de temps.',
                        'emoji' => true,
                    ],
                ],
            ],
        ];

        $this->slack->sendBlocks($event->getSociete(), $blocks);
    }

    public function planningTaskNotCompleted(PlanningTaskNotCompletedNotification $event): void
    {
        $buttonLink = $this->urlGenerator->generate(
            'app_fo_projet_planning',
            ['projetId' => $event->getProjet()->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $blocks = [
            [
                'type' => 'section',
                'text' => [
                    'type' => 'mrkdwn',
                    'text' => "Bonjour ! La date d'échéance de la tâche *{$event->getProjetPlanningTask()->getText()}* du projet *{$event->getProjet()->getAcronyme()}* est dans *3 jours*.",
                ],
            ],
            [
                'type' => 'actions',
                'elements' => [
                    [
                        'type' => 'button',
                        'style' => 'primary',
                        'text' => [
                            'type' => 'plain_text',
                            'text' => 'Planification du projet',
                        ],
                        'value' => 'btn_saisie_temps',
                        'url' => $buttonLink,
                    ],
                ],
            ],
        ];

        $this->slack->sendBlocks($event->getSociete(), $blocks);
    }
}

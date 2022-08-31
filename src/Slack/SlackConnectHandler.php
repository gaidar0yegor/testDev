<?php

namespace App\Slack;

use App\Entity\User;
use App\Security\Exception\UnexpectedUserException;
use Doctrine\ORM\EntityManagerInterface;
use JoliCode\Slack\Exception\SlackErrorResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Security;

class SlackConnectHandler
{
    private Slack $slack;

    private EntityManagerInterface $em;

    private Security $security;

    private FlashBagInterface $flashBag;

    public function __construct(
        Slack $slack,
        EntityManagerInterface $em,
        Security $security,
        FlashBagInterface $flashBag
    ) {
        $this->slack = $slack;
        $this->em = $em;
        $this->security = $security;
        $this->flashBag = $flashBag;
    }

    public function handleRequest(Request $request): void
    {
        if (!$request->query->has('code')) {
            return;
        }

        try {
            $slackAccessToken = $this->slack->requestAccessToken($request->query->get('code'));

            if ($this->slack->isDirectMessage($slackAccessToken)) {
                $this->flashBag->add(
                    'warning',
                    "
                        L'accès n'a pas été ajouté : veuillez selectionner une chaîne, et non pas un utilisateur.
                        L'application RDI-Manager n'est pas configurée pour envoyer un message direct à une personne.
                    "
                );
                return;
            }

            $user = $this->security->getUser();

            if (!$user instanceof User) {
                throw new UnexpectedUserException($user);
            }

            $slackAccessToken->setSociete($user->getSociete());

            $this->em->persist($slackAccessToken);
            $this->em->flush();

            $this->flashBag->add('success', 'Connexion à Slack réussie !');
        } catch (SlackErrorResponse $e) {
            $this->flashBag->add('error', 'Erreur lors de la connexion à Slack : '.$e->getMessage());
        } catch (UnexpectedUserException $e) {
            $this->flashBag->add('error', 'Impossible de se connecter à Slack, aucun utilisateur RDI-Manager actuellement connecté.');
        }
    }
}

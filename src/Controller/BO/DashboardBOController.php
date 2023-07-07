<?php

namespace App\Controller\BO;

use App\Activity\Type\OverflowQuotasBoActivity;
use App\Activity\Type\ProjetCreatedBoActivity;
use App\Activity\Type\SocieteCreatedBoActivity;
use App\Entity\BoUserNotification;
use App\Entity\Projet;
use App\Entity\Societe;
use App\Entity\User;
use App\MultiSociete\UserContext;
use App\Repository\BoUserNotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;
use App\Repository\ProjetRepository;
use App\Repository\SocieteRepository;

class DashboardBOController extends AbstractController
{
    private EntityManagerInterface $em;
    private UserContext $userContext;
    private BoUserNotificationRepository $boUserNotificationRepository;

    public function __construct(UserContext $userContext, EntityManagerInterface $em, BoUserNotificationRepository $boUserNotificationRepository)
    {
        $this->em = $em;
        $this->userContext = $userContext;
        $this->boUserNotificationRepository = $boUserNotificationRepository;
    }

    /**
     * @Route("/dashboard", name="corp_app_bo_dashboard")
     */
    public function dashboardUser()
    {
        $limit = 30;

        $societeNotifs = $this->em->getRepository(BoUserNotification::class)->findByUserByActivityType(
            $this->userContext->getUser(),
            SocieteCreatedBoActivity::getType(),
            $limit
        );

        $projetNotifs = $this->em->getRepository(BoUserNotification::class)->findByUserByActivityType(
            $this->userContext->getUser(),
            ProjetCreatedBoActivity::getType(),
            $limit
        );

        $quotaNotifs = $this->em->getRepository(BoUserNotification::class)->findByUserByActivityType(
            $this->userContext->getUser(),
            OverflowQuotasBoActivity::getType(),
            $limit
        );

        $this->boUserNotificationRepository->acknowledgeAllFor($this->userContext->getUser());

        $users = $this->em->getRepository(User::class)->findCreatedAt((new \DateTime())->format('Y'));
        $userData = [];
        for ($index = 1; $index < 13; $index++) {
            $userData[$index] = array_search($index, array_column($users, 'mois')) !== false
                ? $users[array_search($index, array_column($users, 'mois'))]['total']
            : 0;
        }

        $projets = $this->em->getRepository(Projet::class)->findCreatedAt((new \DateTime())->format('Y'));
        $projetData = [];
        for ($index = 1; $index < 13; $index++) {
            $projetData[$index] = array_search($index, array_column($projets, 'mois')) !== false
                ? $projets[array_search($index, array_column($projets, 'mois'))]['total']
                : 0;
        }

        $societes = $this->em->getRepository(Societe::class)->findCreatedAt((new \DateTime())->format('Y'));
        $societeData = [];
        for ($index = 1; $index < 13; $index++) {
            $societeData[$index] = array_search($index, array_column($societes, 'mois')) !== false
                ? $societes[array_search($index, array_column($societes, 'mois'))]['total']
                : 0;
        }

        return $this->render('bo/dashboard/dashboard.html.twig',[
            'societeNotifs' => $societeNotifs,
            'projetNotifs' => $projetNotifs,
            'quotaNotifs' => $quotaNotifs,
            'nbrUsers' => $this->em->getRepository(User::class)->getCountAll(),
            'nbrProjets' => $this->em->getRepository(Projet::class)->getCountAll(),
            'nbrSocietes' => $this->em->getRepository(Societe::class)->getCountAll(),
            'userCreatedAt' => $userData,
            'projetCreatedAt' => $projetData,
            'societeCreatedAt' => $societeData,
        ]);
    }

    /**
     * @Route("/stats", name="corp_app_bo_stats")
     */
    public function stats()
    {
        return $this->render('bo/stats/stats.html.twig');
    }

}
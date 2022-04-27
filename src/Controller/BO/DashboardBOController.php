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
     * @Route("/dashboard", name="app_bo_dashboard")
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

        return $this->render('bo/dashboard/dashboard.html.twig',[
            'societeNotifs' => $societeNotifs,
            'projetNotifs' => $projetNotifs,
            'quotaNotifs' => $quotaNotifs,
            'nbrUsers' => count($this->em->getRepository(User::class)->findAll()),
            'nbrProjets' => count($this->em->getRepository(Projet::class)->findAll()),
            'nbrSocietes' => count($this->em->getRepository(Societe::class)->findAll()),
        ]);
    }

    /**
     * @Route("/stats", name="app_bo_stats")
     */
    public function stats(ProjetRepository $projetRepository, UserRepository $userRepository, SocieteRepository $societeRepository)
    {
        $userCreatedAt = $this->em->getRepository(User::class)->findCreatedAt((new \DateTime())->format('Y'));

        $userData = [];

        for ($index = 1; $index < 13; $index++) {
            $userData[$index] = 0;
        }

        foreach ($userCreatedAt as $user) {
            $userData[$user['mois']] = $user['total'];
        }

        $projetCreatedAt = $this->em->getRepository(Projet::class)->findCreatedAt((new \DateTime())->format('Y'));

        $projetData = [];

        for ($index = 1; $index < 13; $index++) {
            $projetData[$index] = 0;
        }

        foreach ($projetCreatedAt as $projet) {
            $projetData[$projet['mois']] = $projet['total'];
        }

        $societeCreatedAt = $this->em->getRepository(Societe::class)->findCreatedAt((new \DateTime())->format('Y'));

        $societeData = [];

        for ($index = 1; $index < 13; $index++) {
            $societeData[$index] = 0;
        }

        foreach ($societeCreatedAt as $societe) {
            $societeData[$societe['mois']] = $societe['total'];
        }

        return $this->render('bo/stats/stats.html.twig',[
            'userCreatedAt' => $userData,
            'projetCreatedAt' => $projetData,
            'societeCreatedAt' => $societeData,
        ]);
    }

}
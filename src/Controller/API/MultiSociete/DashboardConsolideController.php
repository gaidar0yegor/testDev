<?php

namespace App\Controller\API\MultiSociete;

use App\Activity\ActivityService;
use App\Entity\ProjetActivity;
use App\Entity\SocieteUser;
use App\Repository\ProjetActivityRepository;
use App\Repository\ProjetRepository;
use App\Security\Role\RoleProjet;
use App\Service\StatisticsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/multiSociete/dashboard/consolide")
 */
class DashboardConsolideController extends AbstractController
{

}

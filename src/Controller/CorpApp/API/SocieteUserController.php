<?php

namespace App\Controller\CorpApp\API;

use App\Entity\Societe;
use App\Entity\SocieteUser;
use App\Entity\SocieteUserPeriod;
use App\Listener\UseCache;
use App\Repository\SocieteUserNotificationRepository;
use App\Repository\SocieteUserEvenementNotificationRepository;
use App\Security\Voter\SameUserVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use App\Security\Voter\TeamManagementVoter;
use Doctrine\ORM\EntityManagerInterface;
use App\Exception\RdiException;

/**
 * @Route("/api")
 */
class SocieteUserController extends AbstractController
{
    /**
     * Modal de l'ajout de la date d'entree pour l'admin societe
     *
     * @Route(
     *      "/societe-user-date-entree/{societeId}/{societeUserId}",
     *      methods={"POST"},
     *      name="api_societe_user_date_entree_update"
     * )
     * 
     * @ParamConverter("societe", options={"id" = "societeId"})
     * @ParamConverter("societeUser", options={"id" = "societeUserId"})
     */
    public function updateDateEntree(
        Societe $societe,
        SocieteUser $societeUser,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted(TeamManagementVoter::NAME, $societeUser);
        
        if ( 
            !$this->isCsrfTokenValid('update-date-entree-admin', $request->get('token')) ||
            $societeUser->getSociete() !== $societe
            ) {
            throw new BadRequestHttpException('Csrf token invalid');
        }

        $dateEntree = $request->get('date-entree');

        if (!$dateEntree || !\DateTime::createFromFormat('d/m/Y', $dateEntree)) {
            throw new BadRequestHttpException('Date entree is required');
        }

        if($societeUser->getSocieteUserPeriods()->count() === 0){
            $societeUserPeriod = SocieteUserPeriod::create(\DateTime::createFromFormat('d/m/Y', $dateEntree));
            $societeUser->addSocieteUserPeriod($societeUserPeriod);
        } elseif($societeUser->getSocieteUserPeriods()->last()->getDateEntry() === null){
            $societeUserPeriod = $societeUser->getSocieteUserPeriods()->last();
            $societeUserPeriod->setDateEntry(\DateTime::createFromFormat('d/m/Y', $dateEntree));
        } else {
            throw new RdiException('Une erreur est survenue !');
        }

        $em->persist($societeUser);
        $em->flush();

        $this->addFlash('success', "Votre date d'entrée dans la société a bien été enregistrée");

        return $this->redirectToRoute('app_home');
    }
}

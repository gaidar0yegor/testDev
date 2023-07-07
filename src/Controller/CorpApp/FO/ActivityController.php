<?php

namespace App\Controller\CorpApp\FO;

use App\Entity\Activity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ActivityController extends AbstractController
{
    /**
     * @Route(
     *      "activite/{id}/supprimer",
     *      name="activite_fo_delete",
     *      methods={"POST"}
     * )
     */
    public function delete(
        Activity $activity,
        EntityManagerInterface $em
    ) {

        $em->remove($activity);
        $em->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}

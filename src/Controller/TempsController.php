<?php

namespace App\Controller;

use App\Form\TempsPassesType;
use App\Repository\UserRepository;
use App\Service\DateMonthService;
use App\Service\TempsPasseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TempsController extends AbstractController
{
    /**
     * @Route("/temps", name="temps_")
     */
    public function saisieTempsEnPourCent(Request $request, UserRepository $userRepository, TempsPasseService $tempsPasseService, DateMonthService $dateMonthService)
    {
        // $user = $this->getUser(); Quand l'auth sera fonctionnelle
        $user = $userRepository->findOneBy(['email' => 'user1@eureka.com']);
        $mois = $dateMonthService->getCurrentMonth();

        $listeTempsPasses = $tempsPasseService->loadTempsPasses($user, $mois);
        $form = $this->createForm(TempsPassesType::class, $listeTempsPasses);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            foreach ($listeTempsPasses->getTempsPasses() as $tempsPasse) {
                $em->persist($tempsPasse);
            }

            $em->flush();

            return $this->redirectToRoute('temps_');
        }

        return $this->render('temps/temps_en_pour_cent.html.twig', [
            'mois' => $mois,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/absences", name="absences_")
     */
    public function saisieAbsences()
    {
        return $this->render('temps/absences.html.twig', [
            'controller_name' => 'TempsController',
        ]);
    }
}

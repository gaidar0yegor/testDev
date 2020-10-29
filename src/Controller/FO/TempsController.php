<?php

namespace App\Controller\FO;

use App\Exception\MonthOutOfRangeException;
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
     * @Route(
     * "/temps/{year}/{month}",
     * requirements={"year"="\d{4}", "month"="\d{2}"},
     * name="temps_"
     * )
     */
    public function saisieTempsEnPourCent(
        Request $request,
        string $year = null,
        string $month = null,
        UserRepository $userRepository,
        TempsPasseService $tempsPasseService,
        DateMonthService $dateMonthService
    ) {
        if ($year !== null && $month === null) {
            return $this->redirectToRoute('temps_');
        }

        try {
            $mois = $dateMonthService->getMonthFromYearAndMonth($year, $month);
        } catch (MonthOutOfRangeException $e) {
            throw $this->createNotFoundException($e->getMessage());
        }

        $listeTempsPasses = $tempsPasseService->loadTempsPasses($this->getUser(), $mois);
        $form = $this->createForm(TempsPassesType::class, $listeTempsPasses);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            foreach ($listeTempsPasses->getTempsPasses() as $tempsPasse) {
                $em->persist($tempsPasse);
            }

            $em->flush();

            return $this->redirectToRoute('temps_', [
                'year' => $year,
                'month' => $month,
            ]);
        }

        return $this->render('temps/temps_en_pour_cent.html.twig', [
            'mois' => $mois,
            'form' => $form->createView(),
            'next' => $dateMonthService->getNextMonth($mois),
            'prev' => $dateMonthService->getPrevMonth($mois),
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

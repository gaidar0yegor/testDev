<?php

namespace App\Controller;

use App\DTO\RecommandationMessage;
use App\Form\RecommandationMessageType;
use App\Service\RdiMailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class RecommanderController extends AbstractController
{
    private string $defaultMailFrom;

    public function __construct(string $defaultMailFrom)
    {
        $this->defaultMailFrom = $defaultMailFrom;
    }

    /**
     * @Route("/recommander-rdi-manager", name="app_recommander")
     */
    public function recommander(Request $request, RdiMailer $rdiMailer)
    {
        $recommandationMessage = new RecommandationMessage();

        $recommandationMessage->setFrom($this->defaultMailFrom);

        if ($this->getUser()) {
            $recommandationMessage->setFrom($this->getUser()->getEmail());
        }

        $form = $this->createForm(RecommandationMessageType::class, $recommandationMessage);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $rdiMailer->sendRecommandationEmail($recommandationMessage);

            $this->addFlash('success', sprintf(
                'Un email de recommandation a bien été envoyé à "%s". Merci !',
                $recommandationMessage->getTo()
            ));

            return $this->redirectToRoute('app_recommander');
        }

        return $this->render('recommander/recommander.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

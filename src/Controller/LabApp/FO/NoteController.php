<?php

namespace App\Controller\LabApp\FO;

use App\Entity\LabApp\Etude;
use App\Entity\LabApp\Note;
use App\EtudeResourceInterface;
use App\Form\LabApp\NoteType;
use App\MultiSociete\UserContext;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class NoteController extends AbstractController
{
    private EventDispatcherInterface $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @Route("/etude/{etudeId}/note/ajouter", name="lab_app_fo_note_ajouter", methods={"GET","POST"})
     *
     * @ParamConverter("etude", options={"id" = "etudeId"})
     */
    public function new(
        Etude $etude,
        Request $request,
        EntityManagerInterface $em,
        TranslatorInterface $translator,
        UserContext $userContext
    ): Response {
        $this->denyAccessUnlessGranted(EtudeResourceInterface::CREATE, $etude);

        $note = new Note();
        $note
            ->setEtude($etude)
            ->setCreatedBy($userContext->getUserBook())
            ->setDate(new \DateTime())
        ;

        $form = $this->createForm(NoteType::class, $note);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($note);
            $em->flush();

            $this->addFlash('success', $translator->trans(
                "La note \"{title_note}\" a été ajoutée à l'étude.",
                [
                    'title_note' => $note->getTitle(),
                ]
            ));

            return $this->redirectToRoute('lab_app_fo_etude', [
                'id' => $etude->getId(),
            ]);
        }

        return $this->render('lab_app/note/post_edit.html.twig', [
            'note' => $note,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/notes/{id}/modifier", name="lab_app_fo_note_modifier", methods={"GET","POST"})
     */
    public function edit(
        Note $note,
        Request $request,
        TranslatorInterface $translator,
        EntityManagerInterface $em
    )
    {
        $this->denyAccessUnlessGranted(EtudeResourceInterface::EDIT, $note);

        $form = $this->createForm(NoteType::class, $note);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', $translator->trans(
                'La note "{title_note}" a été modifiée.',
                [
                    'title_note' => $note->getTitle(),
                ]
            ));

            return $this->redirectToRoute('lab_app_fo_etude', [
                'id' => $note->getEtude()->getId(),
                '_fragment' => 'note-'.$note->getId(),
            ]);
        }

        return $this->render('lab_app/note/post_edit.html.twig', [
            'note' => $note,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/notes/{id}", name="lab_app_fo_note_delete", methods={"DELETE"})
     */
    public function delete(
        Request $request,
        Note $note,
        TranslatorInterface $translator,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted(EtudeResourceInterface::DELETE, $note);

        $etude = $note->getEtude();

        if ($this->isCsrfTokenValid('delete'.$note->getId(), $request->request->get('_token'))) {
            $title = $note->getTitle();
            $em->remove($note);
            $em->flush();

            $this->addFlash('success', $translator->trans(
                'La note "{title_note}" a été supprimée.',
                [
                    'title_note' => $title,
                ]
            ));
        }

        return $this->redirectToRoute('lab_app_fo_etude', [
            'id' => $etude->getId(),
        ]);
    }
}

<?php

namespace App\Controller\BO;

use App\DTO\InitSociete;
use App\Entity\Societe;
use App\Entity\SocieteUser;
use App\Form\InitSocieteType;
use App\Form\UserEmailType;
use App\License\DTO\License;
use App\License\Factory\OffreStarterLicenseFactory;
use App\License\LicenseService;
use App\LicenseGeneration\Exception\EncryptionKeysException;
use App\LicenseGeneration\Form\GenerateLicenseType;
use App\LicenseGeneration\LicenseGeneration;
use App\Repository\SocieteRepository;
use App\Security\Role\RoleSociete;
use App\Service\FileResponseFactory;
use App\Service\Invitator;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;

class SocieteController extends AbstractController
{
    /**
     * @Route("/societes", name="app_bo_societes")
     */
    public function societes(SocieteRepository $societeRepository)
    {
        return $this->render('bo/societes/societes.html.twig', [
            'societes' => $societeRepository->findAll(),
        ]);
    }

    /**
     * @Route("/societes/{id}", name="app_bo_societe", requirements={"id"="\d+"})
     */
    public function societe(
        Request $request,
        Societe $societe,
        Invitator $invitator,
        EntityManagerInterface $em,
        LicenseService $licenseService
    ): Response {
        $admin = $invitator->initUser($societe, RoleSociete::ADMIN);
        $form = $this->createForm(UserEmailType::class, $admin);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $invitator->check($admin, $form);

            $em->persist($admin);
            $em->flush();

            $this->addFlash('success', "
                L'administrateur a été ajouté !
                Vous pouvez lui envoyer un email d'invitation
                afin qu'il finalise son inscription.
            ");

            return $this->redirectToRoute('app_bo_societe', [
                'id' => $societe->getId(),
            ]);
        }

        $this->addWarningIfNotInvitationSent($societe);

        return $this->render('bo/societes/societe.html.twig', [
            'societe' => $societe,
            'form' => $form->createView(),
            'licenses' => $licenseService->retrieveAllLicenses($societe),
        ]);
    }

    /**
     * @Route("/societes/{id}/generer-license", name="app_bo_societe_generate_license", requirements={"id"="\d+"})
     */
    public function societeGenerateLicense(
        Request $request,
        Societe $societe,
        LicenseGeneration $licenseGeneration,
        OffreStarterLicenseFactory $licenseFactory,
        LicenseService $licenseService
    ) {
        $license = $licenseFactory->createLicense($societe);
        $form = $this->createForm(GenerateLicenseType::class, $license);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $licenseContent = $licenseGeneration->generateLicenseFile($license);
                $licenseService->storeLicense($licenseContent);
            } catch (EncryptionKeysException $e) {
                throw new HttpException(
                    Response::HTTP_NOT_IMPLEMENTED,
                    'La génération de licenses semble ne pas être configurée.',
                    $e
                );
            }

            $this->addFlash(
                'success',
                'Une nouvelle license a été générée et ajoutée à la société '.$societe->getRaisonSociale()
            );

            return $this->redirectToRoute('app_bo_societe', [
                'id' => $societe->getId(),
            ]);
        }

        return $this->render('bo/societes/generate-license.html.twig', [
            'form' => $form->createView(),
            'societe' => $societe,
        ]);
    }

    private function addWarningIfNotInvitationSent(Societe $societe): void
    {
        foreach ($societe->getAdmins() as $admin) {
            if (null !== $admin->getInvitationSentAt() || null === $admin->getInvitationToken()) {
                return;
            }
        }

        $raisonSociale = $societe->getRaisonSociale();

        $this->addFlash('warning', "
            Aucun administrateur de la société $raisonSociale n'a encore pas reçu de notifications.
            Envoyez un email d'invitation depuis cette page
            afin qu'il puisse finaliser son inscription !
        ");
    }

    /**
     * @Route(
     *      "/societes/{societeId}/envoi-invitation/{societeUserId}",
     *      name="app_bo_societe_invite",
     *      methods={"POST"},
     *      requirements={"id"="\d+"}
     * )
     *
     * @ParamConverter("societe", options={"id" = "societeId"})
     * @ParamConverter("societeUser", options={"id" = "societeUserId"})
     */
    public function societeSendInvitation(
        Request $request,
        Societe $societe,
        SocieteUser $societeUser,
        Invitator $invitator,
        EntityManagerInterface $em
    ) {
        if (!$this->isCsrfTokenValid('send-invitation-admin', $request->get('token'))) {
            throw new BadRequestHttpException('Csrf token invalid');
        }

        $invitator->sendInvitation($societeUser, $this->getUser());
        $em->flush();

        $this->addFlash('success', sprintf(
            'Un email avec un lien d\'invitation a été envoyé à l\'administrateur "%s".',
            $societeUser->getInvitationEmail()
        ));

        return $this->redirectToRoute('app_bo_societe', [
            'id' => $societe->getId(),
        ]);
    }

    /**
     * @Route("/societes/creer", name="app_bo_societes_creer")
     */
    public function create(
        Request $request,
        Invitator $invitator,
        EntityManagerInterface $em
    ) {
        $initSociete = new InitSociete();
        $form = $this->createForm(InitSocieteType::class, $initSociete);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $societe = $invitator->initSociete($initSociete);

            $societe
                ->setCreatedFrom(Societe::CREATED_FROM_BACK_OFFICE)
                ->setCreatedBy($this->getUser())
            ;

            $invitator->check($societe, $form);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($societe);
            $em->flush();

            $this->addFlash('success', sprintf(
                    'La société "%s" a bien été créée.',
                    $societe->getRaisonSociale()
            ));

            return $this->redirectToRoute('app_bo_societe', [
                'id' => $societe->getId(),
            ]);
        }

        return $this->render('bo/societes/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route(
     *      "/telecharger/{filename}",
     *      name="app_bo_license_download",
     *      requirements={"filename"=".*"}
     * )
     */
    public function download(
        string $filename,
        LicenseService $licenseService,
        FileResponseFactory $fileResponseFactory
    ): Response {
        $licenseContent = $licenseService->readLicenseFile($filename);

        return $fileResponseFactory->createFileResponseFromString(
            $licenseContent,
            basename($filename),
            'text/plain'
        );
    }
}

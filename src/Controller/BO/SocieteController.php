<?php

namespace App\Controller\BO;

use App\DTO\InitSociete;
use App\Entity\Societe;
use App\Entity\SocieteUser;
use App\Form\InitSocieteType;
use App\Form\OnboardingNotificationEveryType;
use App\Form\UserEmailType;
use App\License\Factory\PremiumLicenseFactory;
use App\License\Factory\StandardLicenseFactory;
use App\License\Factory\StarterLicenseFactory;
use App\License\LicenseService;
use App\LicenseGeneration\Exception\EncryptionKeysException;
use App\LicenseGeneration\Form\GenerateLicenseType;
use App\LicenseGeneration\LicenseGeneration;
use App\Notification\Event\SocieteDisabledNotification;
use App\Notification\Event\SocieteEnabledNotification;
use App\Notification\Event\SocieteProductModifiedNotification;
use App\Repository\SocieteRepository;
use App\Security\Role\RoleSociete;
use App\File\FileResponseFactory;
use App\Service\Invitator;
use App\SocieteProduct\Product\PremiumProduct;
use App\SocieteProduct\Product\ProductPrivileges;
use App\SocieteProduct\Product\StandardProduct;
use App\SocieteProduct\Product\StarterProduct;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SocieteController extends AbstractController
{
    private EventDispatcherInterface $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @Route("/societes", name="corp_app_bo_societes")
     */
    public function societes(SocieteRepository $societeRepository) {

        return $this->render('bo/societes/societes.html.twig', [
            'societes' => $societeRepository->findAll(),
        ]);
    }

    /**
     * @Route("/societes/{id}", name="corp_app_bo_societe", requirements={"id"="\d+"})
     */
    public function societe(
        Request $request,
        Societe $societe,
        Invitator $invitator,
        EntityManagerInterface $em,
        LicenseService $licenseService,
        ProductPrivileges $productPrivileges
    ): Response {
        $admin = $invitator->initUser($societe, RoleSociete::ADMIN);

        $formUserEmail = $this->createForm(UserEmailType::class, $admin);
        $formUserEmail->handleRequest($request);

        $formOnboardingNotificationEvery = $this->createForm(OnboardingNotificationEveryType::class, $societe);
        $formOnboardingNotificationEvery->handleRequest($request);

        if ($formUserEmail->isSubmitted() && $formUserEmail->isValid()) {
            $invitator->check($admin, $formUserEmail);

            $em->persist($admin);
            $em->flush();

            $this->addFlash('success', "
                L'administrateur a été ajouté !
                Vous pouvez lui envoyer un email d'invitation
                afin qu'il finalise son inscription.
            ");

            return $this->redirectToRoute('corp_app_bo_societe', [
                'id' => $societe->getId(),
            ]);
        }

        if ($formOnboardingNotificationEvery->isSubmitted() && $formOnboardingNotificationEvery->isValid()) {
            $em->persist($societe);
            $em->flush();

            $this->addFlash('success', 'Interval d\'envoi des emails d\'onboarding mis à jour.');

            return $this->redirectToRoute('corp_app_bo_societe', [
                'id' => $societe->getId(),
            ]);
        }

        $this->addWarningIfNotInvitationSent($societe);

        return $this->render('bo/societes/societe.html.twig', [
            'societe' => $societe,
            'formUserEmail' => $formUserEmail->createView(),
            'formOnboardingNotificationEvery' => $formOnboardingNotificationEvery->createView(),
            'licenses' => $licenseService->retrieveAllLicenses($societe),
            'societeProducts' => $productPrivileges->getAllProducts()
        ]);
    }

    /**
     * @Route("/societes/{id}/generer-license/{product}",
     *     name="corp_app_bo_societe_generate_license",
     *     requirements={"id"="\d+", "product": "^STARTER|STANDARD|PREMIUM$"},
     *     )
     */
    public function societeGenerateLicense(
        Request $request,
        Societe $societe,
        string $product,
        LicenseGeneration $licenseGeneration,
        StarterLicenseFactory $starterLicenseFactory,
        StandardLicenseFactory $standardLicenseFactory,
        PremiumLicenseFactory $premiumLicenseFactory,
        LicenseService $licenseService
    ) {
        switch ($product){
            case StarterProduct::PRODUCT_KEY:
                $license = $starterLicenseFactory->createLicense($societe);
                break;
            case StandardProduct::PRODUCT_KEY:
                $license = $standardLicenseFactory->createLicense($societe);
                break;
            case PremiumProduct::PRODUCT_KEY:
                $license = $premiumLicenseFactory->createLicense($societe);
                break;
            default:
                throw new HttpException(
                    Response::HTTP_NOT_IMPLEMENTED,
                    'La génération de licenses semble ne pas être configurée.'
                );
        }

        $oldLicenses = $licenseService->retrieveAllLicenses($societe);
        if (count($oldLicenses) > 0){
            $license->setExpirationDate(reset($oldLicenses)->getExpirationDate());
            $license->setQuotas(reset($oldLicenses)->getQuotas());
            $license->setIsTryLicense(reset($oldLicenses)->getIsTryLicense());
        }

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

            $oldLicense = count($oldLicenses) > 0 ? reset($oldLicenses) : null;
            $this->dispatcher->dispatch(new SocieteProductModifiedNotification($societe, $oldLicense, $license));

            $this->addFlash(
                'success',
                'Une nouvelle license a été générée et ajoutée à la société '.$societe->getRaisonSociale()
            );

            return $this->redirectToRoute('corp_app_bo_societe', [
                'id' => $societe->getId(),
            ]);
        }

        return $this->render('bo/societes/generate-license.html.twig', [
            'form' => $form->createView(),
            'societe' => $societe,
            'license' => $license,
        ]);
    }

    /**
     * @Route("/societes/{id}/désactiver",
     *     name="corp_app_bo_societe_disable",
     *     requirements={"id"="\d+"},
     *    methods={"POST"}
     *     )
     */
    public function societeDisable(
        Request $request,
        Societe $societe,
        TranslatorInterface $translator,
        EntityManagerInterface $em
    )
    {
        if (!$this->isCsrfTokenValid('disable_societe_'.$societe->getId(), $request->get('_token'))) {
            $this->addFlash('error', $translator->trans('csrf_token_invalid'));

            return $this->redirectToRoute('corp_app_bo_societe', [
                'id' => $societe->getId(),
            ]);
        }

        $societe->setEnabled(false);
        $societe->setDisabledAt(new \DateTime());
        $em->persist($societe);
        $em->flush();

        $this->dispatcher->dispatch(new SocieteDisabledNotification($societe));

        $this->addFlash('success', $translator->trans('societe_have_been_disbled', [
            'raisonSociale' => $societe->getRaisonSociale(),
        ]));

        return $this->redirectToRoute('corp_app_bo_societe', [
            'id' => $societe->getId(),
        ]);
    }

    /**
     * @Route("/societes/{id}/réactiver",
     *     name="corp_app_bo_societe_enable",
     *     requirements={"id"="\d+"},
     *    methods={"POST"}
     *     )
     */
    public function societeEnable(
        Request $request,
        Societe $societe,
        TranslatorInterface $translator,
        EntityManagerInterface $em
    )
    {
        if (!$this->isCsrfTokenValid('enable_societe_'.$societe->getId(), $request->get('_token'))) {
            $this->addFlash('error', $translator->trans('csrf_token_invalid'));

            return $this->redirectToRoute('corp_app_bo_societe', [
                'id' => $societe->getId(),
            ]);
        }

        $societe->setEnabled(true);
        $societe->setDisabledAt(null);
        $societe->setOnStandBy(false);
        $em->persist($societe);
        $em->flush();

        $this->dispatcher->dispatch(new SocieteEnabledNotification($societe));

        $this->addFlash('success', $translator->trans('societe_have_been_enabled', [
            'raisonSociale' => $societe->getRaisonSociale(),
        ]));

        return $this->redirectToRoute('corp_app_bo_societe', [
            'id' => $societe->getId(),
        ]);
    }

    /**
     * @Route("/societes/{id}/mettre-en-veille",
     *     name="corp_app_bo_societe_stand_by",
     *     requirements={"id"="\d+"},
     *    methods={"POST"}
     *     )
     */
    public function societeStandBy(
        Request $request,
        Societe $societe,
        TranslatorInterface $translator,
        EntityManagerInterface $em
    )
    {
        if (!$this->isCsrfTokenValid('stand_by_societe_'.$societe->getId(), $request->get('_token'))) {
            $this->addFlash('error', $translator->trans('csrf_token_invalid'));

            return $this->redirectToRoute('corp_app_bo_societe', [
                'id' => $societe->getId(),
            ]);
        }

        $societe->setEnabled(false);
        $societe->setOnStandBy(true);
        $em->persist($societe);
        $em->flush();

        $this->addFlash('success', $translator->trans('societe_have_been_puted_on_stand_by', [
            'raisonSociale' => $societe->getRaisonSociale(),
        ]));

        return $this->redirectToRoute('corp_app_bo_societe', [
            'id' => $societe->getId(),
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
     *      name="corp_app_bo_societe_invite",
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
        if (
            !$this->isCsrfTokenValid('send-invitation-admin', $request->get('token')) ||
            $societeUser->getSociete() !== $societe
        ) {
            throw new BadRequestHttpException('Csrf token invalid');
        }

        $invitator->sendInvitation($societeUser, $this->getUser());
        $em->flush();

        $this->addFlash('success', sprintf(
            'Un email avec un lien d\'invitation a été envoyé à l\'administrateur "%s".',
            $societeUser->getInvitationEmail()
        ));

        return $this->redirectToRoute('corp_app_bo_societe', [
            'id' => $societe->getId(),
        ]);
    }

    /**
     * @Route(
     *      "/societes/{societeId}/invitation/{societeUserId}/supprimer",
     *      name="corp_app_bo_societe_invite_delete",
     *      methods={"POST"},
     *      requirements={"id"="\d+"}
     * )
     *
     * @ParamConverter("societe", options={"id" = "societeId"})
     * @ParamConverter("societeUser", options={"id" = "societeUserId"})
     */
    public function societeInviteDelete(
        Request $request,
        Societe $societe,
        SocieteUser $societeUser,
        EntityManagerInterface $em
    ) {
        if (
            !$this->isCsrfTokenValid('delete-invitation-admin', $request->get('token')) ||
            $societeUser->getSociete() !== $societe
        ) {
            throw new BadRequestHttpException('Csrf token invalid');
        }

        if (!$societeUser->getInvitationToken()){
            $this->addFlash('warning', 'Vous ne pouvez pas supprimer une invitation acceptée.');
        } else {
            $invitationEmail = $societeUser->getInvitationEmail();
            $em->remove($societeUser);
            $em->flush();

            $this->addFlash('success', sprintf(
                'L\'invitation de "%s" a été supprimée avec succès.',
                $invitationEmail
            ));
        }

        return $this->redirectToRoute('corp_app_bo_societe', [
            'id' => $societe->getId(),
        ]);
    }

    /**
     * @Route("/societes/creer", name="corp_app_bo_societes_creer")
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

            return $this->redirectToRoute('corp_app_bo_societe', [
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
     *      name="corp_app_bo_license_download",
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

<?php

namespace App\Controller\CorpApp\FO\Admin;

use App\License\LicenseService;
use App\File\FileResponseFactory;
use App\MultiSociete\UserContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("licenses")
 */
class LicensesController extends AbstractController
{
    /**
     * @Route(
     *      "/",
     *      name="corp_app_fo_admin_licenses"
     * )
     */
    public function index(LicenseService $licenseService, UserContext $userContext)
    {
        $licenses = $licenseService->retrieveAllLicenses($userContext->getSocieteUser()->getSociete());

        return $this->render('corp_app/licenses/index.html.twig', [
            'licenses' => $licenses,
        ]);
    }

    /**
     * @Route(
     *      "/telecharger/{filename}",
     *      name="corp_app_fo_admin_license_download",
     *      requirements={"filename"=".*"}
     * )
     */
    public function download(
        string $filename,
        LicenseService $licenseService,
        UserContext $userContext,
        FileResponseFactory $fileResponseFactory
    ): Response {
        $licenseContent = $licenseService->readLicenseFile($filename);
        $license = $licenseService->parseLicenseContent($licenseContent);
        $user = $userContext->getSocieteUser();

        if (0 !== $user->getSociete()->getUuid()->compareTo($license->getSociete()->getUuid())) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette license.');
        }

        return $fileResponseFactory->createFileResponseFromString(
            $licenseContent,
            basename($filename),
            'text/plain'
        );
    }
}

<?php

namespace App\Controller\FO\Admin;

use App\Entity\User;
use App\Exception\UnexpectedUserException;
use App\License\LicenseService;
use App\Service\FileResponseFactory;
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
     *      name="app_fo_admin_licenses"
     * )
     */
    public function index(LicenseService $licenseService)
    {
        $licenses = $licenseService->retrieveAllLicenses($this->getUser()->getSociete());

        return $this->render('licenses/index.html.twig', [
            'licenses' => $licenses,
        ]);
    }

    /**
     * @Route(
     *      "/telecharger/{filename}",
     *      name="app_fo_admin_license_download",
     *      requirements={"filename"=".*"}
     * )
     */
    public function download(
        string $filename,
        LicenseService $licenseService,
        FileResponseFactory $fileResponseFactory
    ): Response {
        $licenseContent = $licenseService->readLicenseFile($filename);
        $license = $licenseService->parseLicenseContent($licenseContent);

        $user = $this->getUser();

        if (!$user instanceof User) {
            throw new UnexpectedUserException($user);
        }

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

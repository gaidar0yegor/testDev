<?php

namespace App\RegisterSociete\Listener;

use App\MultiSociete\UserContext;
use App\RegisterSociete\RegisterSociete;
use App\Security\Role\RoleSociete;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Called before Register pages, to check current registration state.
 */
class RegisterStepsListener implements EventSubscriberInterface
{
    private RegisterSociete $registerSociete;

    private UserContext $userContext;

    private UrlGeneratorInterface $urlGenerator;

    public function __construct(
        RegisterSociete $registerSociete,
        UserContext $userContext,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->registerSociete = $registerSociete;
        $this->userContext = $userContext;
        $this->urlGenerator = $urlGenerator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        if (!$this->routeStartsWith('corp_app_register', $event)) {
            return;
        }

        $this->beforeController($event);
    }

    private function beforeController(RequestEvent $event): void
    {
        // Starts a new registration if not already set in session
        if (!$this->registerSociete->hasCurrentRegistration()) {
            $this->registerSociete->initializeCurrentRegistration();
        }

        $registration = $this->registerSociete->getCurrentRegistration();
        $route = $event->getRequest()->attributes->get('_route');

        // Do nothing on main route, redirected to next page in controller.
        if ('corp_app_register' === $route) {
            return;
        }

        // Redirect to 'create societe' step if societe not yet created while creating my account
        if ($this->routeStartsWith('corp_app_register_account', $event)) {
            if (null === $registration->societe) {
                $this->redirectToRoute('corp_app_register_societe', $event);
                return;
            }
        }

        // Redirect to first step if no admin logged in
        if (in_array($route, [
            'corp_app_register_projet',
            'corp_app_register_collaborators',
            'corp_app_register_finish',
        ])) {
            if (
                !$this->userContext->hasSocieteUser()
                || $this->userContext->getSocieteUser()->getRole() !== RoleSociete::ADMIN
            ) {
                $this->redirectToRoute('corp_app_register', $event);
                return;
            }
        }

        // Redirect to next step if trying to create a projet when already one is created
        if ('corp_app_register_projet' === $route) {
            if (count($this->userContext->getSocieteUser()->getSociete()->getProjets()) > 0) {
                $this->redirectToRoute('corp_app_register_collaborators', $event);
                return;
            }
        }

        // Redirect to next step if trying to add collaborators if I already invited some
        if ('corp_app_register_projet' === $route) {
            if (count($this->userContext->getSocieteUser()->getSociete()->getSocieteUsers()) > 1) {
                $this->redirectToRoute('corp_app_register_finish', $event);
                return;
            }
        }
    }

    private function redirectToRoute(string $route, RequestEvent $event): void
    {
        $url = $this->urlGenerator->generate($route, [], UrlGeneratorInterface::ABSOLUTE_URL);

        $event->setResponse(new RedirectResponse($url));
    }

    private function routeStartsWith(string $prefix, RequestEvent $event): bool
    {
        $route = $event->getRequest()->attributes->get('_route');

        return substr($route, 0, strlen($prefix)) === $prefix;
    }
}

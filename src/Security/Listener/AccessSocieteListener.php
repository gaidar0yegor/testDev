<?php

namespace App\Security\Listener;

use App\MultiSociete\UserContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Called to verify if societe is enabled.
 */
class AccessSocieteListener implements EventSubscriberInterface
{
    private UserContext $userContext;

    private UrlGeneratorInterface $urlGenerator;

    private TranslatorInterface $translator;

    public function __construct(
        UserContext $userContext,
        UrlGeneratorInterface $urlGenerator,
        TranslatorInterface $translator
    ) {
        $this->userContext = $userContext;
        $this->urlGenerator = $urlGenerator;
        $this->translator = $translator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $route = $event->getRequest()->attributes->get('_route');
        $session = $event->getRequest()->getSession();

        if ($route !== 'corp_app_fo_multi_societe_switch' && $this->userContext->hasSocieteUser())
        {
            if (!$this->userContext->getSocieteUser()->getSociete()->getEnabled()) {
                $session->getFlashBag()->add('warning', $this->translator->trans('societe_disabled_contact_rdi'));
                $this->redirectToRoute('corp_app_fo_multi_societe_switch', $event);
                return;
            }

            if (!$this->userContext->getSocieteUser()->getEnabled()) {
                $session->getFlashBag()->add('warning', 'L\'accès à cette société vous a été désactivé par un administrateur');
                $this->redirectToRoute('corp_app_fo_multi_societe_switch', $event);
                return;
            }
        }

    }

    private function redirectToRoute(string $route, RequestEvent $event): void
    {
        $url = $this->urlGenerator->generate($route, [], UrlGeneratorInterface::ABSOLUTE_URL);

        $event->setResponse(new RedirectResponse($url));
    }
}

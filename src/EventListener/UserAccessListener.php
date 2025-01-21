<?php

namespace App\EventListener;

use App\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserAccessListener
{
    private TokenStorageInterface $tokenStorage;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->urlGenerator = $urlGenerator;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $route = $request->attributes->get('_route');

        // Skip checking these routes
        if (in_array($route, ['app_login', 'app_register', 'app_logout'])) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        if (!$token) {
            return;
        }

        $user = $token->getUser();
        if (!$user instanceof User) {
            return;
        }

        if ($user->isIsBlocked()) {
            $this->tokenStorage->setToken(null);
            $event->setResponse(new RedirectResponse($this->urlGenerator->generate('app_login')));
        }
    }
} 
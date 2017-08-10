<?php

namespace Shopware\Framework\Routing;


use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class UrlRedirectListener implements EventSubscriberInterface
{
    public function onKernelRequest(GetResponseEvent $event): void
    {
        $request = $event->getRequest();

        if (!$request->attributes->has(Router::SEO_REDIRECT_URL)) {
            return;
        }

        $event->stopPropagation();

        $event->setResponse(
            new RedirectResponse(
                $request->attributes->get(Router::SEO_REDIRECT_URL)
            )
        );
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                ['onKernelRequest', 20]
            ],
        ];
    }
}

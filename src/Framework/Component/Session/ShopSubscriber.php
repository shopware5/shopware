<?php

namespace Shopware\Framework\Component\Session;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ShopSubscriber implements EventSubscriberInterface
{
    public function onKernelRequest(GetResponseEvent $event): void
    {
        $request = $event->getRequest();
        if (!$request->hasPreviousSession()) {
            return;
        }

        $shopId = $request->attributes->get('_shop_id');
        if (empty($shopId)) {
            return;
        }

        if ($request->getSession()->isStarted()) {
            return;
        }

        $request->getSession()->setName('session-' . $shopId);
        $request->getSession()->start();
        $request->getSession()->set('sessionId', $request->getSession()->getId());
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 15]],
        ];
    }
}
<?php

namespace Shopware\Storefront\Context;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ContextSubscriber implements EventSubscriberInterface
{
    const SHOP_CONTEXT_PROPERTY = '_shop_context';

    /**
     * @var StorefrontContextServiceInterface
     */
    private $contextService;

    public function __construct(StorefrontContextServiceInterface $contextService)
    {
        $this->contextService = $contextService;
    }

    public function onKernelRequest(GetResponseEvent $event): void
    {
        $request = $event->getRequest();

        $shopId = $request->attributes->get('_shop_id');
        if (!$shopId) {
            return;
        }

        $context = $this->contextService->getShopContext();
        $request->attributes->set(self::SHOP_CONTEXT_PROPERTY, $context);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                ['onKernelRequest', 0]
            ],
        ];
    }
}
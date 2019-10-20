<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Components\Controller;

use Enlight_Controller_Request_RequestHttp as EnlightRequest;
use Shopware\Components\Routing\Router;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RouterListener implements EventSubscriberInterface
{
    private $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function onKernelRequestBefore(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (!$request instanceof EnlightRequest) {
            return;
        }

        $this->setCurrentRequest($request);
    }

    public function onKernelRequestAfter(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (!$request instanceof EnlightRequest) {
            return;
        }

        if ($request->attributes->has('_controller')) {
            // routing is already done
            return;
        }

        $request->attributes->set('_controller', FrontController::class);
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequestBefore', 100], ['onKernelRequestAfter', -100]],
        ];
    }

    private function setCurrentRequest(EnlightRequest $request)
    {
        $this->router->getContext()->updateFromEnlightRequest($request);
    }
}

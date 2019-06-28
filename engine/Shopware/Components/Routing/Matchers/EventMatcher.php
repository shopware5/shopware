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

namespace Shopware\Components\Routing\Matchers;

use Enlight_Controller_Request_RequestHttp as EnlightRequest;
use Enlight_Event_EventManager as EnlightEventManager;
use Shopware\Components\Routing\Context;
use Shopware\Components\Routing\MatcherInterface;

class EventMatcher implements MatcherInterface
{
    /**
     * @var EnlightEventManager
     */
    protected $eventManager;

    public function __construct(EnlightEventManager $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    /**
     * {@inheritdoc}
     */
    public function match($pathInfo, Context $context)
    {
        if (strpos($pathInfo, '/backend/') === 0 || strpos($pathInfo, '/api/') === 0) {
            return $pathInfo;
        }
        if ($context->getShopId() === null) { //only frontend
            return $pathInfo;
        }

        $request = EnlightRequest::createFromGlobals();
        $request->setBaseUrl($context->getBaseUrl());
        $request->setPathInfo($pathInfo);

        $event = $this->eventManager->notifyUntil('Enlight_Controller_Router_Route', [
            'request' => $request,
            'context' => $context,
        ]);

        return $event !== null ? $event->getReturn() : false;
    }
}

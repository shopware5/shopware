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

use Shopware\Components\Routing\MatcherInterface;
use Shopware\Components\Routing\Context;
use Enlight_Controller_Request_RequestHttp as EnlightRequest;
use Enlight_Event_EventManager as EnlightEventManager;

/**
 * @category  Shopware
 * @package   Shopware\Components\Routing
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class EventMatcher implements MatcherInterface
{
    /**
     * @var EnlightEventManager
     */
    protected $eventManager;

    /**
     * @param EnlightEventManager $eventManager
     */
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

        $request = new EnlightRequest();
        $request->setBaseUrl($context->getBaseUrl());
        $request->setPathInfo($pathInfo);

        $event = $this->eventManager->notifyUntil('Enlight_Controller_Router_Route', [
            //'subject' => $router, @deprecated someone need it?
            'request' => $request,
            'context' => $context
        ]
        );
        return $event !== null ? $event->getReturn() : false;
    }
}

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

namespace Shopware\Components;

use Enlight\Event\SubscriberInterface;
use Enlight_Event_EventArgs;
use Psr\Link\LinkProviderInterface;
use Symfony\Component\WebLink\HttpHeaderSerializer;

/**
 * Class AddLinkHeaderSubscriber
 * @package Shopware
 */
class AddLinkHeaderSubscriber implements SubscriberInterface
{
    /**
     * @var HttpHeaderSerializer
     */
    private $serializer;

    /**
     * AddLinkHeaderSubscriber constructor.
     */
    public function __construct()
    {
        $this->serializer = new HttpHeaderSerializer();
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Front_DispatchLoopShutdown' => 'onDispatchLoopShutdown'
        ];
    }

    /**
     * @param Enlight_Event_EventArgs $args
     */
    public function onDispatchLoopShutdown(Enlight_Event_EventArgs $args)
    {
        /** @var \Enlight_Controller_Request_Request $request */
        $request = $args->get('request');
        /** @var \Enlight_Controller_Response_Response $response */
        $response = $args->get('response');

        if ($linkProvider = $request->getParam('_links')) {
            if (!$linkProvider instanceof LinkProviderInterface || !$links = $linkProvider->getLinks()) {
                return;
            }
            $response->setHeader('Link', $this->serializer->serialize($links));
        }
    }
}
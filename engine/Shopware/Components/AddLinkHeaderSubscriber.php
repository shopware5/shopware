<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Components;

use Enlight\Event\SubscriberInterface;
use Enlight_Controller_Request_Request;
use Enlight_Controller_Response_Response;
use Enlight_Event_EventArgs;
use Psr\Link\LinkProviderInterface;
use Symfony\Component\WebLink\HttpHeaderSerializer;
use Traversable;

class AddLinkHeaderSubscriber implements SubscriberInterface
{
    /**
     * @var HttpHeaderSerializer
     */
    private $serializer;

    /**
     * @var bool
     */
    private $pushEnabled;

    /**
     * @var WebLinkManager
     */
    private $webLinkManager;

    public function __construct(
        HttpHeaderSerializer $headerSerializer,
        WebLinkManager $webLinkManager,
        bool $pushEnabled
    ) {
        $this->serializer = $headerSerializer;
        $this->pushEnabled = $pushEnabled;
        $this->webLinkManager = $webLinkManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'Enlight_Controller_Front_DispatchLoopShutdown' => 'onDispatchLoopShutdown',
        ];
    }

    public function onDispatchLoopShutdown(Enlight_Event_EventArgs $args): void
    {
        /** @var Enlight_Controller_Request_Request $request */
        $request = $args->get('request');

        // Only use Server Push if it is enabled in the settings and the current module is "frontend"
        if (!$this->pushEnabled
            || $request->getModuleName() !== 'frontend') {
            return;
        }

        /** @var Enlight_Controller_Response_Response $response */
        $response = $args->get('response');

        $linkProvider = $this->webLinkManager->getLinkProvider();
        if (!$linkProvider instanceof LinkProviderInterface) {
            return;
        }

        $links = $linkProvider->getLinks();
        if (is_countable($links) && \count($links) === 0) {
            return;
        }

        if ($links instanceof Traversable && iterator_count($links) === 0) {
            return;
        }

        $response->headers->set('link', $this->serializer->serialize($links));
    }
}

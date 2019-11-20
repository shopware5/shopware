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

namespace Shopware\Components\License\Service;

use Enlight\Event\SubscriberInterface;
use Enlight_Controller_Action;
use Enlight_Event_EventArgs;
use Enlight_Plugin_PluginManager;
use Shopware\Components\License\Struct\ShopwareEdition;
use Shopware_Plugins_Backend_Auth_Bootstrap;

class LicenseServiceSubscriber implements SubscriberInterface
{
    /**
     * @var ShopwareEditionServiceInterface
     */
    private $shopwareEditionService;

    /**
     * @var Enlight_Plugin_PluginManager
     */
    private $plugins;

    public function __construct(
        ShopwareEditionServiceInterface $shopwareEditionService,
        Enlight_Plugin_PluginManager $plugins
    ) {
        $this->shopwareEditionService = $shopwareEditionService;
        $this->plugins = $plugins;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Index' => 'onPostDispatchBackendIndex',
        ];
    }

    public function onPostDispatchBackendIndex(Enlight_Event_EventArgs $args)
    {
        /** @var Enlight_Controller_Action $controller */
        $controller = $args->get('subject');
        $edition = $this->hasBackendLogin() ? $this->shopwareEditionService->getProductEdition() : ShopwareEdition::CE;
        $controller->View()->assign('product', $edition);
    }

    protected function hasBackendLogin(): bool
    {
        /** @var Shopware_Plugins_Backend_Auth_Bootstrap $authPlugin */
        $authPlugin = $this->plugins->get('Backend')->get('Auth');

        return $authPlugin->checkAuth() !== null;
    }
}

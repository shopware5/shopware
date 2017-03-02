<?php
declare(strict_types=1);

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

namespace Shopware\Components\DependencyInjection\Bridge;

use Shopware\Components\Auth\BackendAuthSubscriber;
use Shopware\Components\DependencyInjection\Container;

class Auth
{
    /**
     * @param Container                 $container
     * @param BackendAuthSubscriber     $subscriber
     * @param \Enlight_Template_Manager $templateManager
     *
     * @return \Shopware_Components_Auth
     */
    public static function createAuth(
        Container $container,
        BackendAuthSubscriber $subscriber,
        \Enlight_Template_Manager $templateManager
    ): \Shopware_Components_Auth {
        $container->load('backend_session');

        $resource = \Shopware_Components_Auth::getInstance();
        $adapter = new \Shopware_Components_Auth_Adapter_Default();
        $storage = new \Zend_Auth_Storage_Session('Shopware', 'Auth');
        $resource->setBaseAdapter($adapter);
        $resource->addAdapter($adapter);
        $resource->setStorage($storage);

        $templateManager->unregisterPlugin(
            \Smarty::PLUGIN_FUNCTION,
            'acl_is_allowed'
        );

        $templateManager->registerPlugin(
            \Enlight_Template_Manager::PLUGIN_FUNCTION,
            'acl_is_allowed',
            [$subscriber, 'isAllowed']
        );

        return $resource;
    }
}

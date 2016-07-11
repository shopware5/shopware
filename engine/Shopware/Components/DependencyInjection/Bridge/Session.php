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

namespace Shopware\Components\DependencyInjection\Bridge;

use Shopware\Components\DependencyInjection\Container;

/**
 * Session Dependency Injection Bridge
 * Starts and handles the session
 *
 * @category  Shopware
 * @package   Shopware\Components\DependencyInjection\Bridge
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Session
{
    /**
     * @param Container $container
     * @return \Enlight_Components_Session_Namespace
     */
    public function factory(Container $container)
    {
        $sessionOptions = $container->getParameter('shopware.session');

        if (!empty($sessionOptions['unitTestEnabled'])) {
            \Enlight_Components_Session::$_unitTestEnabled = true;
        }
        unset($sessionOptions['unitTestEnabled']);

        if (\Enlight_Components_Session::isStarted()) {
            \Enlight_Components_Session::writeClose();
        }

        /** @var $shop \Shopware\Models\Shop\Shop */
        $shop = $container->get('Shop');

        $name = 'session-' . $shop->getId();
        $sessionOptions['name'] = $name;

        $mainShop = $shop->getMain() ?: $shop;
        if ($mainShop->getAlwaysSecure()) {
            $sessionOptions['cookie_secure'] = true;
        }

        if (!isset($sessionOptions['save_handler']) || $sessionOptions['save_handler'] == 'db') {
            $config_save_handler = array(
                'db'             => $container->get('Db'),
                'name'           => 's_core_sessions',
                'primary'        => 'id',
                'modifiedColumn' => 'modified',
                'dataColumn'     => 'data',
                'lifetimeColumn' => 'expiry'
            );
            \Enlight_Components_Session::setSaveHandler(
                new \Enlight_Components_Session_SaveHandler_DbTable($config_save_handler)
            );
            unset($sessionOptions['save_handler']);
        }

        \Enlight_Components_Session::start($sessionOptions);

        $container->set('SessionID', \Enlight_Components_Session::getId());

        $namespace = new \Enlight_Components_Session_Namespace('Shopware');
        $namespace->offsetSet('sessionId', \Enlight_Components_Session::getId());

        return $namespace;
    }
}

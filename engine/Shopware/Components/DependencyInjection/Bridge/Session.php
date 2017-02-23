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
use Shopware\Components\Session\PdoSessionHandler;

/**
 * Session Dependency Injection Bridge
 * Starts and handles the session
 *
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Session
{
    /**
     * @param Container $container
     * @param string    $table
     * @param array     $sessionOptions
     *
     * @return null|\SessionHandlerInterface
     */
    public function createSaveHandler(Container $container, $table, array $sessionOptions)
    {
        if (isset($sessionOptions['save_handler']) && $sessionOptions['save_handler'] !== 'db') {
            return null;
        }

        $dbOptions = $container->getParameter('shopware.db');
        $conn = Db::createPDO($dbOptions);

        return new PdoSessionHandler(
            $conn,
            [
                'db_table' => $table,
                'db_id_col' => 'id',
                'db_data_col' => 'data',
                'db_expiry_col' => 'expiry',
                'db_time_col' => 'modified',
                'lock_mode' => $sessionOptions['locking'] ? PdoSessionHandler::LOCK_TRANSACTIONAL : PdoSessionHandler::LOCK_NONE,
            ]
        );
    }

    /**
     * @param Container                $container
     * @param \SessionHandlerInterface $saveHandler
     *
     * @return \Enlight_Components_Session_Namespace
     */
    public function createSession(Container $container, \SessionHandlerInterface $saveHandler = null)
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
        if ($mainShop->getSecure()) {
            $sessionOptions['cookie_secure'] = true;
        }

        if ($saveHandler) {
            session_set_save_handler($saveHandler);
            unset($sessionOptions['save_handler']);
        }

        unset($sessionOptions['locking']);

        \Enlight_Components_Session::start($sessionOptions);

        $container->set('SessionID', \Enlight_Components_Session::getId());

        $namespace = new \Enlight_Components_Session_Namespace('Shopware');
        $namespace->offsetSet('sessionId', \Enlight_Components_Session::getId());

        return $namespace;
    }

    /**
     * @param \Shopware_Components_Config   $config
     * @param \Enlight_Controller_Front     $controller
     * @param array                         $options
     * @param \SessionHandlerInterface|null $saveHandler
     *
     * @return \Enlight_Components_Session_Namespace
     */
    public function createBackendSession(
        \Shopware_Components_Config $config,
        array $options,
        \SessionHandlerInterface $saveHandler = null,
        \Enlight_Controller_Front $controller = null
    ) {
        $options = $this->getSessionOptions($config, $options, $controller);
        if ($saveHandler) {
            session_set_save_handler($saveHandler);
        }

        \Enlight_Components_Session::start($options);

        return new \Enlight_Components_Session_Namespace('ShopwareBackend');
    }

    /**
     * @param \Shopware_Components_Config    $config
     * @param array                          $options
     * @param \Enlight_Controller_Front|null $controller
     *
     * @return array
     */
    private function getSessionOptions(
        \Shopware_Components_Config $config,
        array $options,
        \Enlight_Controller_Front $controller = null
    ) {
        if (!isset($options['cookie_path']) && $controller && $controller->Request() !== null) {
            $options['cookie_path'] = rtrim($controller->Request()->getBaseUrl(), '/') . '/backend/';
        }

        if (empty($options['gc_maxlifetime'])) {
            $backendTimeout = $config->get('backendTimeout', 60 * 90);
            $options['gc_maxlifetime'] = (int) $backendTimeout ?: PHP_INT_MAX;
        }

        unset($options['locking']);

        return $options;
    }
}

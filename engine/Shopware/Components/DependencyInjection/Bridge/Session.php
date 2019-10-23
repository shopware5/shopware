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

use Enlight_Components_Session as EnlightSession;
use Enlight_Components_Session_Namespace as SessionNamespace;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Components\Session\PdoSessionHandler;
use Shopware\Models\Shop\Shop;

/**
 * Session Dependency Injection Bridge
 * Starts and handles the session
 */
class Session
{
    /**
     * @return \SessionHandlerInterface|null
     */
    public function createSaveHandler(Container $container)
    {
        $sessionOptions = $container->getParameter('shopware.session');
        if (isset($sessionOptions['save_handler']) && $sessionOptions['save_handler'] !== 'db') {
            return null;
        }

        $dbOptions = $container->getParameter('shopware.db');
        $conn = Db::createPDO($dbOptions);

        return new PdoSessionHandler(
            $conn,
            [
                'db_table' => 's_core_sessions',
                'db_id_col' => 'id',
                'db_data_col' => 'data',
                'db_expiry_col' => 'expiry',
                'db_time_col' => 'modified',
                'lock_mode' => $sessionOptions['locking'] ? PdoSessionHandler::LOCK_TRANSACTIONAL : PdoSessionHandler::LOCK_NONE,
            ]
        );
    }

    /**
     * @return SessionNamespace
     */
    public function createSession(Container $container, \SessionHandlerInterface $saveHandler = null)
    {
        $sessionOptions = $container->getParameter('shopware.session');

        if (!empty($sessionOptions['unitTestEnabled'])) {
            EnlightSession::$_unitTestEnabled = true;
        }
        unset($sessionOptions['unitTestEnabled']);

        if (EnlightSession::isStarted()) {
            EnlightSession::writeClose();
        }

        /** @var Shop $shop */
        $shop = $container->get('shop');
        $mainShop = $shop->getMain() ?: $shop;

        $name = 'session-' . $shop->getId();

        if ($container->get('config')->get('shareSessionBetweenLanguageShops')) {
            $name = 'session-' . $mainShop->getId();
        }

        $sessionOptions['name'] = $name;
        $basePath = $mainShop->getBasePath();
        $sessionOptions['cookie_path'] = empty($basePath) ? '/' : $basePath;

        if ($mainShop->getSecure()) {
            $sessionOptions['cookie_secure'] = true;
        }

        if ($saveHandler) {
            session_set_save_handler($saveHandler);
            unset($sessionOptions['save_handler']);
        }

        unset($sessionOptions['locking']);

        EnlightSession::start($sessionOptions);

        $sessionId = EnlightSession::getId();
        $container->set('sessionid', $sessionId);

        $namespace = new SessionNamespace('Shopware');
        $namespace->offsetSet('sessionId', $sessionId);

        return $namespace;
    }
}

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

namespace Shopware\Components\DependencyInjection\Bridge;

use Enlight_Components_Session_Namespace;
use RuntimeException;
use SessionHandlerInterface;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Components\Session\PdoSessionHandler;
use Shopware_Components_Config;
use Symfony\Component\HttpFoundation\Session\Attribute\NamespacedAttributeBag;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

/**
 * Session Dependency Injection Bridge
 * Starts and handles the session
 */
class Session
{
    /**
     * @return SessionHandlerInterface|null
     */
    public function createSaveHandler(Container $container)
    {
        $sessionOptions = (array) $container->getParameter('shopware.session');
        if (isset($sessionOptions['save_handler']) && $sessionOptions['save_handler'] !== 'db') {
            return null;
        }

        $dbOptions = $container->getParameter('shopware.db');
        if (!\is_array($dbOptions)) {
            throw new RuntimeException('Parameter shopware.db has to be an array');
        }

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
     * @return Enlight_Components_Session_Namespace
     */
    public function createSession(Container $container, ?SessionHandlerInterface $saveHandler = null)
    {
        // If another session is already started, save and close it before starting the frontend session below.
        // We need to do this, because the other session would use the session id of the frontend session and thus write
        // its data into the wrong session.
        Enlight_Components_Session_Namespace::ensureBackendSessionClosed($container);
        // Ensure no session is active before starting the frontend session below. We need to do this because there
        // could be another session with inconsistent/invalid state in the container.
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
            // The empty session id signals to `Enlight_Components_Session_Namespace::start()` that the session cookie
            // should be used as session id.
            session_id('');
        }

        $sessionOptions = $container->getParameter('shopware.session');

        if (!\is_array($sessionOptions)) {
            throw new RuntimeException('Parameter shopware.session has to be an array');
        }

        /** @var \Shopware\Models\Shop\Shop $shop */
        $shop = $container->get('shop');
        $mainShop = $shop->getMain() ?: $shop;

        $name = 'session-' . $shop->getId();

        if ($container->get(Shopware_Components_Config::class)->get('shareSessionBetweenLanguageShops')) {
            $name = 'session-' . $mainShop->getId();
        }

        $sessionOptions['name'] = $name;

        if ($mainShop->getSecure()) {
            $sessionOptions['cookie_secure'] = true;
        }

        $basePath = $mainShop->getBasePath();
        $sessionOptions['cookie_path'] = empty($basePath) ? '/' : $basePath;

        if ($saveHandler) {
            if (empty($sessionOptions['unitTestEnabled'])) {
                session_set_save_handler($saveHandler);
            }
            unset($sessionOptions['save_handler']);
        }

        unset($sessionOptions['locking']);

        if (isset($sessionOptions['save_path'])) {
            ini_set('session.save_path', (string) $sessionOptions['save_path']);
        }

        if (isset($sessionOptions['save_handler'])) {
            ini_set('session.save_handler', (string) $sessionOptions['save_handler']);
        }

        $storage = new NativeSessionStorage($sessionOptions, $saveHandler);

        if (!empty($sessionOptions['unitTestEnabled']) || session_status() === PHP_SESSION_ACTIVE) {
            $storage = new MockArraySessionStorage();
        }

        $attributeBag = new NamespacedAttributeBag('Shopware');

        $session = new Enlight_Components_Session_Namespace($storage, $attributeBag);
        $session->start();
        $session->set('sessionId', $session->getId());

        $container->set('sessionid', $session->getId());

        $requestStack = $container->get('request_stack');

        if ($requestStack->getCurrentRequest()) {
            $requestStack->getCurrentRequest()->setSession($session);
        }

        return $session;
    }
}

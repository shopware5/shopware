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
use Shopware\Components\Session\SessionInterface;
use Shopware\Models\Shop\Shop;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\PhpBridgeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;

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
     * @return SessionInterface
     */
    public function factory(Container $container)
    {
        $session = new \Enlight_Components_Session_Namespace(
            $container->get('session.storage'),
            new AttributeBag('Shopware')
        );

        $container->set('SessionID', $session->getId());

        /** @var \Enlight_Event_EventManager $eventManager */
        $eventManager = $container->get('events');
        $eventManager->addListener(
            'Enlight_Bootstrap_AfterRegisterResource_Shop',
            array($this, 'onAfterRegisterShop'),
            -100
        );

        return $session;
    }

    /**
     * @param Container $container
     * @return SessionStorageInterface
     */
    public function factoryStorage(Container $container)
    {
        $sessionOptions = $this->getOptions($container);

        if (!empty($sessionOptions['unitTestEnabled'])) {
            $storage = new PhpBridgeSessionStorage();
        } else {
            $storage = new NativeSessionStorage();
        }

        $this->setSessionStorageOptions($container, $storage, $sessionOptions);

        return $storage;
    }


    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function onAfterRegisterShop(\Enlight_Event_EventArgs $args)
    {
        /** @var $container Container */
        $container = $args->get('subject');
        if ($container->initialized('session')) {
            /** @var SessionInterface $session */
            $session = $container->get('session');
            $sessionName = $this->getSessionName($container);
            if ($session->getName() != $sessionName) {
                if ($session->isStarted()) {
                    $session->save();
                }
                $storage = $container->get('session.storage');
                if ($storage instanceof NativeSessionStorage) {
                    $this->setSessionStorageOptions($container, $storage, $this->getOptions($container));
                }
            }
        }
    }

    /**
     * @see NativeSessionStorage::setOptions
     * @see NativeSessionStorage::setSaveHandler
     * @param $container
     * @param NativeSessionStorage $storage
     * @param $sessionOptions
     */
    private function setSessionStorageOptions($container, NativeSessionStorage $storage, $sessionOptions)
    {
        $storage->setOptions($sessionOptions);

        if (!isset($sessionOptions['save_handler']) || $sessionOptions['save_handler'] == 'db') {
            $storage->setSaveHandler($this->getDbSaveHandler($container));
        } else {
            ini_set('session.save_handler', $sessionOptions['save_handler']);
            if (isset($sessionOptions['save_path'])) {
                ini_set('session.save_path', $sessionOptions['save_path']);
            }
            $storage->setSaveHandler();
        }
    }

    /**
     * @param Container $container
     * @return array
     */
    private function getOptions(Container $container)
    {
        if ($container->has('shop')) {
            /** @var Shop $shop */
            $shop = $container->get('shop');
            $options = $container->getParameter('shopware.session');
            if (empty($options['name'])) {
                $options['name'] = $this->getSessionName($container);
            }
            $mainShop = $shop->getMain() ?: $shop;
            if ($mainShop->getAlwaysSecure()) {
                $options['cookie_secure'] = true;
            }
        } else {
            $options = $this->getBackendOptions($container);
        }
        return $options;
    }

    /**
     * @param Container $container
     * @return string
     */
    private function getSessionName($container)
    {
        if ($container->has('shop')) {
            if ($container->hasParameter('shopware.session.name')) {
                return $container->getParameter('shopware.session.name');
            }
            return 'session-' . $container->get('shop')->getId();
        } else {
            return $container->getParameter('shopware.backendsession.name');
        }
    }

    /**
     * Filters and transforms the session options array
     * so it complies with the format expected by Enlight_Components_Session
     *
     * @param Container $container
     * @return array
     */
    private function getBackendOptions(Container $container)
    {
        $options = $container->getParameter('shopware.backendsession');
        /** @var \Enlight_Controller_Request_Request $request */
        $request = $container->get('front')->Request();

        if (!isset($options['cookie_path']) && $request !== null) {
            $options['cookie_path'] = rtrim($request->getBaseUrl(), '/') . '/backend/';
        }
        if (empty($options['gc_maxlifetime'])) {
            $backendTimeout = $container->get('config')->get('backendTimeout', 60 * 90);
            $options['gc_maxlifetime'] = (int)$backendTimeout ?: PHP_INT_MAX;
        }

        return $options;
    }

    /**
     * @param $container
     * @return PdoSessionHandler
     */
    private function getDbSaveHandler(Container $container)
    {
        $config = [
            'db_table' => 's_core_sessions',
            'db_id_col' => 'id',
            'db_data_col' => 'data',
            'db_lifetime_col' => 'expiry',
            'db_time_col' => 'modified',
            'lock_mode' => PdoSessionHandler::LOCK_ADVISORY
        ];
        if (!$container->has('shop')) {
            $config['db_table'] .= '_backend';
        }
        return new PdoSessionHandler(
            $container->get('db_connection'), $config
        );
    }
}

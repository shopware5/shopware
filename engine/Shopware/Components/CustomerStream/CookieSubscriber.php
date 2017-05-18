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

namespace Shopware\Components\CustomerStream;

use Doctrine\DBAL\Connection;
use Enlight\Event\SubscriberInterface;
use Enlight_Controller_Request_Request as Request;
use Ramsey\Uuid\Uuid;
use Shopware\Components\DependencyInjection\Container;

class CookieSubscriber implements SubscriberInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var Container
     */
    private $container;

    /**
     * @param Connection $connection
     * @param Container  $container
     */
    public function __construct(Connection $connection, Container $container)
    {
        $this->connection = $connection;
        $this->container = $container;
    }

    public static function getSubscribedEvents()
    {
        return [
            'Shopware_Modules_Admin_Login_Successful' => 'afterLogin',
            'Shopware_Modules_Admin_Logout_Successful' => 'afterLogout',
            'Enlight_Controller_Front_RouteStartup' => 'checkCookie',
        ];
    }

    public function afterLogout()
    {
        if (!$this->container->initialized('front')) {
            return;
        }

        /** @var \Enlight_Controller_Front $controller */
        $controller = $this->container->get('front');

        $request = $controller->Request();

        if ($this->container->initialized('session')) {
            $session = $this->container->get('session');
            $session->offsetSet('auto-user', null);
        }

        $controller->Response()->setCookie(
            'slt',
            null,
            strtotime('-1 Year'),
            $request->getBasePath() . '/',
            $this->getHost($request)
        );
    }

    public function checkCookie(\Enlight_Controller_EventArgs $args)
    {
        $request = $args->getRequest();

        $config = $this->container->get('config');

        if (!$config->get('useSltCookie')) {
            return;
        }

        if (!$this->container->initialized('session')) {
            return;
        }

        $session = $this->container->get('session');
        if (!$token = $request->getCookie('slt')) {
            $session->offsetSet('auto-user', null);

            return;
        }
        if ($session->offsetGet('auto-user')) {
            return;
        }

        $data = $this->connection->fetchAssoc('SELECT id, customergroup FROM s_user WHERE login_token = :token LIMIT 1', [':token' => $token]);
        if (!$data) {
            return;
        }

        $session->offsetSet('sUserGroup', $data['customergroup']);
        $session->offsetSet('auto-user', (int) $data['id']);
    }

    public function afterLogin(\Enlight_Event_EventArgs $args)
    {
        $config = $this->container->get('config');

        if (!$config->get('useSltCookie')) {
            return;
        }

        $user = $args->get('user');
        $id = $user['id'];

        if (!$this->container->initialized('front')) {
            return;
        }

        /** @var \Enlight_Controller_Front $controller */
        $controller = $this->container->get('front');

        $response = $controller->Response();

        $request = $controller->Request();

        $token = Uuid::uuid4();

        $expire = time() + 365 * 24 * 60 * 60;

        $session = $this->container->get('session');
        $session->offsetSet('auto-user', null);

        $response->setCookie(
            'slt',
            $token,
            $expire,
            $request->getBasePath() . '/',
            $this->getHost($request)
        );

        $this->connection->update('s_user', ['login_token' => $token], ['id' => $id]);
    }

    /**
     * @param $request
     */
    private function getHost(Request $request)
    {
        return ($request->getHttpHost() === 'localhost') ? null : $request->getHttpHost();
    }
}

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

namespace Shopware\Components\CustomerStream;

use Doctrine\DBAL\Connection;
use Enlight\Event\SubscriberInterface;
use Enlight_Controller_EventArgs;
use Enlight_Controller_Front;
use Enlight_Event_EventArgs;
use Ramsey\Uuid\Uuid;
use Shopware_Components_Config;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Cookie;

class CookieSubscriber implements SubscriberInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(Connection $connection, ContainerInterface $container)
    {
        $this->connection = $connection;
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
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

        /** @var Enlight_Controller_Front $controller */
        $controller = $this->container->get('front');

        if ($this->container->initialized('session')) {
            $session = $this->container->get('session');
            $session->offsetSet('auto-user', null);
        }

        $controller->Response()->headers->setCookie(
            new Cookie('slt', null, strtotime('-1 Year'), $controller->Request()->getBasePath() . '/')
        );
    }

    public function checkCookie(Enlight_Controller_EventArgs $args)
    {
        $request = $args->getRequest();

        $config = $this->container->get(Shopware_Components_Config::class);

        if (!$config->get('useSltCookie')) {
            return;
        }

        if (!$this->container->initialized('session')) {
            return;
        }

        $session = $this->container->get('session');
        if (!$token = $args->getRequest()->getCookie('slt')) {
            $session->offsetSet('auto-user', null);

            return;
        }

        $context = $this->container->get(\Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface::class)->getShopContext();
        if ($context === null) {
            return;
        }

        if ($context->getShop()->hasCustomerScope()) {
            $parts = explode('.', $token);

            if ((int) $context->getShop()->getParentId() !== (int) $parts[1]) {
                return;
            }
        }

        if ($session->offsetGet('auto-user')) {
            return;
        }

        $data = $this->connection->fetchAssoc(
            'SELECT id, customergroup FROM s_user WHERE login_token = :token LIMIT 1',
            [':token' => $token]
        );
        if (!$data) {
            return;
        }

        $session->offsetSet('sUserGroup', $data['customergroup']);
        $session->offsetSet('auto-user', (int) $data['id']);
    }

    public function afterLogin(Enlight_Event_EventArgs $args)
    {
        $config = $this->container->get(Shopware_Components_Config::class);

        if (!$config->get('useSltCookie')) {
            return;
        }

        $user = $args->get('user');
        $id = $user['id'];

        if (!$this->container->initialized('front')) {
            return;
        }

        /** @var Enlight_Controller_Front $controller */
        $controller = $this->container->get('front');

        $response = $controller->Response();

        $request = $controller->Request();

        $context = $this->container->get(\Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface::class)->getShopContext();
        $token = Uuid::uuid4()->toString();
        $token .= '.' . $context->getShop()->getParentId();

        $expire = time() + 365 * 24 * 60 * 60;

        $session = $this->container->get('session');
        $session->offsetSet('auto-user', null);
        $session->offsetSet('userInfo', null);

        $controller->Response()->headers->setCookie(
            new Cookie(
                'slt',
                $token,
                $expire,
                $controller->Request()->getBasePath() . '/',
                null,
                $controller->Request()->isSecure(),
                true
            )
        );

        $this->connection->update('s_user', ['login_token' => $token], ['id' => $id]);
    }
}

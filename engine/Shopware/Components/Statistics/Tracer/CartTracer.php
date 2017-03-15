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

namespace Shopware\Components\Statistics\Tracer;

use Doctrine\DBAL\Connection;
use Enlight_Controller_Request_Request as Request;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Components\Statistics\StatisticTracerInterface;

class CartTracer implements StatisticTracerInterface
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Container  $container
     * @param Connection $connection
     */
    public function __construct(Container $container, Connection $connection)
    {
        $this->container = $container;
        $this->connection = $connection;
    }

    public function trace(Request $request, ShopContextInterface $context)
    {
        $currentController = $request->getParam(
            'requestController',
            $request->getControllerName()
        );

        if (empty($currentController)) {
            return;
        }
        if (!$this->container->initialized('session')) {
            return;
        }

        $sessionId = $this->container->get('session')->get('sessionId');

        if (empty($sessionId)) {
            return;
        }

        $customerId = (int) Shopware()->Session()->sUserId;

        $userAgent = (string) $request->getServer('HTTP_USER_AGENT');

        $this->connection->executeUpdate(
            'UPDATE s_order_basket SET lastviewport = ?, useragent = ?, userID = ? WHERE sessionID = ?',
            [$currentController, $userAgent, $customerId, $sessionId]
        );
    }
}

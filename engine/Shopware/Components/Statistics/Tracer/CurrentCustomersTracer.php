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

class CurrentCustomersTracer implements StatisticTracerInterface
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
        $customerId = 0;
        if ($this->container->initialized('session')) {
            $session = $this->container->get('session');
            $customerId = $session->get('sUserId');
        }

        $this->connection->executeUpdate(
            'INSERT INTO s_statistics_currentusers (remoteaddr, page, `time`, userID, deviceType) VALUES (?, ?, NOW(), ?, ?)',
            [
                $request->getClientIp(),
                $request->getParam('requestPage', $request->getRequestUri()),
                !empty($customerId) ? (int) $customerId : 0,
                $request->getDeviceType(),
            ]
        );
    }
}

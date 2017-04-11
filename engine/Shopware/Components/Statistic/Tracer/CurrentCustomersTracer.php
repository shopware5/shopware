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

namespace Shopware\Components\Statistic\Tracer;

use Doctrine\DBAL\Connection;
use Enlight_Controller_Request_Request as Request;
use Shopware\Bundle\StoreFrontBundle\Context\ShopContextInterface;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Components\Statistic\StatisticTracerInterface;

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

    public function traceRequest(Request $request, ShopContextInterface $context): void
    {
        $this->cleanup();

        $customerId = 0;
        if ($this->container->initialized('session')) {
            $customerId = $this->container->get('session')->get('sUserId');
        }

        $this->connection->executeUpdate(
            'INSERT INTO s_statistics_currentusers (remoteaddr, page, `time`, userID, deviceType) VALUES (:ip, :page, NOW(), :customerId, :device)',
            [
                ':ip' => $request->getClientIp(),
                ':page' => $request->getParam('requestPage', $request->getRequestUri()),
                ':customerId' => !empty($customerId) ? (int) $customerId : 0,
                ':device' => $request->getDeviceType(),
            ]
        );
    }

    private function cleanup(): void
    {
        $this->connection->executeUpdate(
            'DELETE FROM s_statistics_currentusers WHERE time < DATE_SUB(NOW(), INTERVAL 3 MINUTE)'
        );
    }
}

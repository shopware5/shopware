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
use Shopware\Components\Statistics\StatisticTracerInterface;

class VisitorTracer implements StatisticTracerInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function trace(Request $request, ShopContextInterface $context)
    {
        $clientIp = $request->getClientIp();

        $deviceType = $request->getDeviceType();

        $shopId = $context->getShop()->getId();

        $sql = '
            SELECT 1
            FROM s_statistics_visitors
            WHERE datum = CURDATE()
            AND shopID = :shopId
            AND deviceType = :deviceType';

        $exists = $this->connection->fetchColumn(
            $sql,
            [':shopId' => $shopId, ':deviceType' => $deviceType]
        );

        if (empty($exists)) {
            $this->connection->executeUpdate(
                'INSERT INTO s_statistics_visitors (datum, shopID, pageimpressions, uniquevisits, deviceType)
                VALUES(NOW(), :shopId, 1, 1, :deviceType)',
                [':shopId' => $shopId, ':deviceType' => $deviceType]
            );
        }

        $sql = 'SELECT 1 FROM s_statistics_pool WHERE datum = CURDATE() AND remoteaddr = ?';
        $inPool = $this->connection->fetchColumn($sql, [$clientIp]);

        if ($inPool) {
            $sql = 'UPDATE s_statistics_visitors SET pageimpressions = pageimpressions+1 WHERE datum = CURDATE() AND shopID = ? AND deviceType = ?';
            $this->connection->executeUpdate($sql, [$shopId, $deviceType]);

            return;
        }

        $sql = 'INSERT INTO s_statistics_pool (`remoteaddr`, `datum`) VALUES (?, NOW())';
        $this->connection->executeUpdate($sql, [$clientIp]);

        if (!$exists) {
            $sql = 'UPDATE s_statistics_visitors SET pageimpressions = pageimpressions+1, uniquevisits = uniquevisits + 1 WHERE datum = CURDATE() AND shopID = ? AND deviceType = ?';
            $this->connection->executeUpdate($sql, [$shopId, $deviceType]);
        }
    }
}

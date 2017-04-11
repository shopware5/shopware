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
use Shopware\Components\Statistic\StatisticTracerInterface;

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

    public function traceRequest(Request $request, ShopContextInterface $context): void
    {
        $this->cleanup();

        $clientIp = (string) $request->getClientIp();

        $deviceType = (string) $request->getDeviceType();

        $shopId = (int) $context->getShop()->getId();

        $exists = $this->connection->fetchColumn(
            'SELECT 1 FROM s_statistics_visitors
            WHERE datum = CURDATE() AND shopID = :shopId AND deviceType = :deviceType',
            [':shopId' => $shopId, ':deviceType' => $deviceType]
        );

        $newVisitor = $this->isNewVisitor($clientIp);

        if ($newVisitor) {
            $this->addVisitorToPool($clientIp);
        }

        if (!$exists) {
            $this->createNewVisitorRow($shopId, $deviceType);

            return;
        }

        if ($newVisitor) {
            $this->incrementImpressionsAndUniqueVisits($shopId, $deviceType);

            return;
        }

        $this->incrementPageImpressions($shopId, $deviceType);
    }

    /**
     * @param string $clientIp
     *
     * @return bool
     */
    private function isNewVisitor(string $clientIp): bool
    {
        $exists = $this->connection->fetchColumn(
            'SELECT 1 FROM s_statistics_pool WHERE datum = CURDATE() AND remoteaddr = :clientIp',
            [':clientIp' => $clientIp]
        );

        return $exists === false;
    }

    /**
     * @param string $clientIp
     */
    private function addVisitorToPool(string $clientIp): void
    {
        $this->connection->executeUpdate(
            'INSERT INTO s_statistics_pool (`remoteaddr`, `datum`) VALUES (:clientIp, NOW())',
            [':clientIp' => $clientIp]
        );
    }

    /**
     * @param int    $shopId
     * @param string $deviceType
     */
    private function createNewVisitorRow(int $shopId, string $deviceType): void
    {
        $this->connection->executeUpdate(
            'INSERT INTO s_statistics_visitors (datum, shopID, pageimpressions, uniquevisits, deviceType)
             VALUES(NOW(), :shopId, 1, 1, :deviceType)',
            [
                ':shopId' => $shopId,
                ':deviceType' => $deviceType,
            ]
        );
    }

    /**
     * @param int    $shopId
     * @param string $deviceType
     */
    private function incrementImpressionsAndUniqueVisits(int $shopId, string $deviceType): void
    {
        $this->connection->executeUpdate(
            'UPDATE s_statistics_visitors SET pageimpressions = pageimpressions + 1, uniquevisits = uniquevisits + 1 
                 WHERE datum = CURDATE() AND shopID = :shopId AND deviceType = :deviceType',
            [
                ':shopId' => $shopId,
                ':deviceType' => $deviceType,
            ]
        );
    }

    /**
     * @param int    $shopId
     * @param string $deviceType
     */
    private function incrementPageImpressions(int $shopId, string $deviceType): void
    {
        $this->connection->executeUpdate(
            'UPDATE s_statistics_visitors SET pageimpressions = pageimpressions + 1
                 WHERE datum = CURDATE() AND shopID = :shopId AND deviceType = :deviceType',
            [':shopId' => $shopId, ':deviceType' => $deviceType]
        );
    }

    private function cleanup(): void
    {
        $this->connection->executeUpdate('DELETE FROM s_statistics_pool WHERE datum != CURDATE()');
    }
}

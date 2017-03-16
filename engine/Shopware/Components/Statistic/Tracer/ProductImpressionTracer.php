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
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\Statistic\StatisticTracerInterface;

class ProductImpressionTracer implements StatisticTracerInterface
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
        $articleId = (int) $request->getParam('articleId');

        $deviceType = $request->getDeviceType();

        if (!$articleId) {
            return;
        }

        $date = new \DateTime();

        $shopId = $context->getShop()->getId();

        $id = $this->fetchExistingArticleImpression($articleId, $shopId, $date, $deviceType);

        if ($id) {
            $this->connection->executeUpdate(
                'UPDATE s_statistics_article_impression SET impressions = impressions + 1
                 WHERE id = :id',
                [':id' => $id]
            );
            return;
        }

        $this->connection->insert('s_statistics_article_impression', [
            'articleId' => $articleId,
            'shopId' => $shopId,
            'date' => $date->format('Y-m-d'),
            'impressions' => 1,
            'deviceType' => $deviceType
        ]);
    }

    private function fetchExistingArticleImpression(int $articleId, int $shopId, \DateTime $date, string $deviceType): ?int
    {
        $query = $this->connection->createQueryBuilder();
        $query->select('id');
        $query->from('s_statistics_article_impression', 'impressions');
        $query->andWhere('impressions.date = :fromDate');
        $query->andWhere('impressions.articleId = :productId');
        $query->andWhere('impressions.shopId = :shopId');
        $query->andWhere('impressions.deviceType = :deviceType');
        $query->setParameter(':productId', (int) $articleId);
        $query->setParameter(':shopId', (int) $shopId);
        $query->setParameter(':fromDate', $date->format('Y-m-d'));
        $query->setParameter(':deviceType', $deviceType);

        $id = $query->execute()->fetch(\PDO::FETCH_COLUMN);
        if ($id) {
            return (int) $id;
        }
        return null;
    }
}

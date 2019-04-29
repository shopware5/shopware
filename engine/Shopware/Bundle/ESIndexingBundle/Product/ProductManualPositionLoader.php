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

namespace Shopware\Bundle\ESIndexingBundle\Product;

use Doctrine\DBAL\Connection;

class ProductManualPositionLoader implements ProductManualPositionLoaderInterface
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function get(array $productIds): array
    {
        $data = $this->connection->createQueryBuilder()
            ->select('product_id, category_id, position')
            ->from('s_categories_manual_sorting')
            ->where('product_id IN (:ids)')
            ->setParameter('ids', $productIds, Connection::PARAM_INT_ARRAY)
            ->execute()
            ->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_ASSOC);

        foreach ($data as $item) {
            $item['category_id'] = (int) $item['category_id'];
            $item['position'] = (int) $item['position'];
        }

        return $data;
    }
}

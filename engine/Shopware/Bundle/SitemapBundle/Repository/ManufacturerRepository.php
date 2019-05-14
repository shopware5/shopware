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

namespace Shopware\Bundle\SitemapBundle\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class ManufacturerRepository implements ManufacturerRepositoryInterface
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function getManufacturers(ShopContextInterface $shopContext)
    {
        /** @var \PDOStatement $statement */
        $statement = $this->getQueryBuilder($shopContext)->execute();

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryBuilder(ShopContextInterface $shopContext)
    {
        $categoryId = $shopContext->getShop()->getCategory()->getId();

        /** @var QueryBuilder $query */
        $query = $this->connection->createQueryBuilder();
        $query->select(['manufacturer.id', 'manufacturer.name', 'manufacturer.changed']);

        $query->from('s_articles_supplier', 'manufacturer');
        $query->innerJoin('manufacturer', 's_articles', 'product', 'product.supplierID = manufacturer.id')
            ->innerJoin('product', 's_articles_categories_ro', 'categories', 'categories.articleID = product.id AND categories.categoryID = :categoryId')
            ->setParameter(':categoryId', $categoryId);

        $query->groupBy('manufacturer.id');

        return $query;
    }
}

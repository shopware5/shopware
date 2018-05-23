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

namespace Shopware\Bundle\BenchmarkBundle\Provider;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Bundle\BenchmarkBundle\BenchmarkProviderInterface;

class CategoriesProvider implements BenchmarkProviderInterface
{
    /**
     * @var Connection
     */
    private $dbalConnection;

    public function __construct(Connection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function getName()
    {
        return 'categories';
    }

    /**
     * @return array
     */
    public function getBenchmarkData()
    {
        return [
            'total' => $this->getTotalCategories(),
            'maxLevels' => $this->getMaxCategoryDepth(),
            'products' => $this->getCategoryProductData(),
        ];
    }

    /**
     * @return int
     */
    private function getTotalCategories()
    {
        $queryBuilder = $this->dbalConnection->createQueryBuilder();

        return (int) $queryBuilder->select('COUNT(categories.id)')
            ->from('s_categories', 'categories')
            ->execute()
            ->fetchColumn();
    }

    /**
     * @return int
     */
    private function getMaxCategoryDepth()
    {
        $queryBuilder = $this->dbalConnection->createQueryBuilder();
        $paths = $queryBuilder->select('categories.path')
            ->from('s_categories', 'categories')
            ->where('categories.path IS NOT NULL')
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);

        $paths = array_map(function ($path) {
            return substr_count($path, '|');
        }, $paths);

        return (int) max($paths) - 1;
    }

    /**
     * @return array
     */
    private function getCategoryProductData()
    {
        return [
            'average' => $this->getAverageProductsPerCategory(),
            'max' => $this->getMaxProductsPerCategory(),
        ];
    }

    /**
     * @return float
     */
    private function getAverageProductsPerCategory()
    {
        $queryBuilder = $this->dbalConnection->createQueryBuilder();
        $productCountsSql = $this->getProductsInCategoriesCountQueryBuilder()->getSQL();

        $totalProductsInCategories = (int) $queryBuilder->select('SUM(productCounts.productCount) as sumProductsInCategories')
            ->from('(' . $productCountsSql . ')', 'productCounts')
            ->execute()
            ->fetchColumn();

        $totalCategories = $this->getTotalCategories();

        return (float) $totalProductsInCategories / $totalCategories;
    }

    /**
     * @return int
     */
    private function getMaxProductsPerCategory()
    {
        $queryBuilder = $this->getProductsInCategoriesCountQueryBuilder();
        $productsInCategoriesCount = $queryBuilder->execute()->fetchAll(\PDO::FETCH_COLUMN);

        return (int) max($productsInCategoriesCount);
    }

    /**
     * @return QueryBuilder
     */
    private function getProductsInCategoriesCountQueryBuilder()
    {
        $queryBuilder = $this->dbalConnection->createQueryBuilder();

        return $queryBuilder->select('COUNT(categories.articleID) as productCount')
            ->from('s_articles_categories', 'categories')
            ->groupBy('categories.categoryID');
    }
}

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
use Shopware\Bundle\BenchmarkBundle\BenchmarkProviderInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class CategoriesProvider implements BenchmarkProviderInterface
{
    private const NAME = 'categories';

    /**
     * @var Connection
     */
    private $dbalConnection;

    /**
     * @var ShopContextInterface
     */
    private $shopContext;

    /**
     * @var array
     */
    private $categoryIds = [];

    public function __construct(Connection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getBenchmarkData(ShopContextInterface $shopContext)
    {
        $this->shopContext = $shopContext;
        $this->categoryIds = [];

        return [
            'products' => $this->getCategoryProductData(),
            'tree' => $this->getCategoryTree(),
        ];
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
        $totalProductsInCategories = array_sum($this->getProductsInCategoriesCounts());
        $totalCategories = $this->getTotalCategories();

        return (float) $totalProductsInCategories / $totalCategories;
    }

    /**
     * @return int
     */
    private function getTotalCategories()
    {
        $queryBuilder = $this->dbalConnection->createQueryBuilder();
        $categoryIds = $this->getCategoryIds();

        return (int) $queryBuilder->select('COUNT(categories.id)')
            ->from('s_categories', 'categories')
            ->where('categories.id IN (:categoryIds)')
            ->setParameter(':categoryIds', $categoryIds, Connection::PARAM_INT_ARRAY)
            ->execute()
            ->fetchColumn();
    }

    /**
     * @return int
     */
    private function getMaxProductsPerCategory()
    {
        $counts = $this->getProductsInCategoriesCounts();

        return (int) max($counts);
    }

    /**
     * @return array
     */
    private function getProductsInCategoriesCounts()
    {
        $queryBuilder = $this->dbalConnection->createQueryBuilder();
        $categoryIds = $this->getCategoryIds();

        return $queryBuilder->select('COUNT(categories.articleID) as productCount')
            ->from('s_articles_categories', 'categories')
            ->where('categories.categoryID IN (:categoryIds)')
            ->setParameter(':categoryIds', $categoryIds, Connection::PARAM_INT_ARRAY)
            ->groupBy('categories.categoryID')
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * @return array
     */
    private function getCategoryTree()
    {
        $queryBuilder = $this->dbalConnection->createQueryBuilder();
        $categoryIds = $this->getCategoryIds();

        $categories = $queryBuilder->select([
                'categories.id',
                'categories.parent',
                'categories.active',
                'categories.blog as isBlog',
                'categories.hidetop as hiddenFromTopMenu',
                'categories.hidefilter as noFilters',
                'categories.hide_sortings as noSortings',
                'categories.stream_id IS NOT NULL as hasProductStream',
            ])
            ->from('s_categories', 'categories')
            ->where('categories.id IN (:categoryIds)')
            ->setParameter(':categoryIds', $categoryIds, Connection::PARAM_INT_ARRAY)
            ->execute()
            ->fetchAll();

        $categories = array_map(function ($item) {
            $item['active'] = (bool) $item['active'];
            $item['isBlog'] = (bool) $item['isBlog'];
            $item['hiddenFromTopMenu'] = (bool) $item['hiddenFromTopMenu'];
            $item['noFilters'] = (bool) $item['noFilters'];
            $item['noSortings'] = (bool) $item['noSortings'];
            $item['hasProductStream'] = (bool) $item['hasProductStream'];

            return $item;
        }, $categories);

        return $this->buildTree($categories);
    }

    /**
     * @return array
     */
    private function getCategoryIds()
    {
        $shopId = $this->shopContext->getShop()->getId();
        if (array_key_exists($shopId, $this->categoryIds)) {
            return $this->categoryIds[$shopId];
        }

        $categoryQueryBuilder = $this->dbalConnection->createQueryBuilder();
        $categoryId = $this->shopContext->getShop()->getCategory()->getId();

        $this->categoryIds[$shopId] = $categoryQueryBuilder->select('category.id')
            ->from('s_categories', 'category')
            // Match, if path contains the category ID for the given shop
            ->where('category.path LIKE :categoryParentIdLike')
            // Match, if the current category ID equals the category ID for the given shop
            ->orWhere('category.id = :categoryParentId')
            ->orderBy('category.id', 'ASC')
            ->setParameter(':categoryParentId', $categoryId)
            ->setParameter(':categoryParentIdLike', '%|' . $categoryId . '|%')
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);

        return $this->categoryIds[$shopId];
    }

    /**
     * Builds an actual category tree.
     *
     * @return array
     */
    private function buildTree(array $categories)
    {
        $tree = [];

        foreach ($categories as $category) {
            $categoryId = $category['id'];
            $parentId = $category['parent'];

            unset($category['id'], $category['parent']);

            isset($tree[$parentId]) ?: $tree[$parentId] = [];
            isset($tree[$categoryId]) ?: $tree[$categoryId] = [];
            $tree[$parentId][] = array_merge($category, ['children' => &$tree[$categoryId]]);
        }

        return $tree[$categories[0]['parent']];
    }
}

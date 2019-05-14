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

use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Model\QueryBuilder;
use Shopware\Models\Category\Category;

class CategoryRepository implements CategoryRepositoryInterface
{
    /**
     * @var ModelManager
     */
    private $modelManager;

    public function __construct(ModelManager $modelManager)
    {
        $this->modelManager = $modelManager;
    }

    /**
     * @return array
     */
    public function getCategories(ShopContextInterface $shopContext)
    {
        $builder = $this->getQueryBuilder($shopContext);

        $categories = $builder->getQuery()->getArrayResult();

        $categories = array_column($categories, 'category');

        return $categories;
    }

    /**
     * @return QueryBuilder
     */
    public function getQueryBuilder(ShopContextInterface $shopContext)
    {
        $builder = $this->modelManager->createQueryBuilder();
        $builder->from(Category::class, 'c')
            ->select([
                'c as category',
                'attribute',
                'media',
            ])
            ->leftJoin('c.media', 'media', 'media')
            ->leftJoin('c.attribute', 'attribute', 'attribute')
            ->andWhere('c.active = 1');

        $builder
            ->andWhere('c.shops IS NULL OR c.shops LIKE :shopLike')
            ->setParameter(':shopLike', '%|' . $shopContext->getShop()->getId() . '|%');

        $builder->leftJoin('c.customerGroups', 'cg', 'with', 'cg.id = :cgId')
            ->setParameter('cgId', $shopContext->getFallbackCustomerGroup()->getId())
            ->andHaving('COUNT(cg.id) = 0');

        //to prevent a temporary table and file sort we have to set the same sort and group by condition
        $builder->groupBy('c.parentId')
            ->addGroupBy('c.position')
            ->addGroupBy('c.id')
            ->orderBy('c.parentId', 'ASC')
            ->addOrderBy('c.position', 'ASC')
            ->addOrderBy('c.id', 'ASC');

        $builder->andWhere('c.path LIKE :path')
            ->setParameter('path', '%|' . $shopContext->getShop()->getCategory()->getId() . '|%');

        return $builder;
    }
}

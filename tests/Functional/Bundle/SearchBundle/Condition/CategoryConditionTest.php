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

namespace Shopware\Tests\Functional\Bundle\SearchBundle\Condition;

use Shopware\Bundle\SearchBundle\Condition\CategoryCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundleDBAL\ConditionHandler\CategoryConditionHandler;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Category\Category;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\TestCase;

/**
 * @group elasticSearch
 */
class CategoryConditionTest extends TestCase
{
    public function testMultipleCategories()
    {
        $first = $this->helper->createCategory(['name' => 'first-category']);
        $second = $this->helper->createCategory(['name' => 'second-category']);

        $condition = new CategoryCondition([
            $first->getId(),
            $second->getId(),
        ]);

        $this->search(
            [
                'first' => $first,
                'second' => $second,
                'third' => null,
                'fourth' => $first,
            ],
            ['first', 'second', 'fourth'],
            null,
            [$condition]
        );
    }

    public function testConditionCounter(): void
    {
        $queryFactory = Shopware()->Container()->get('shopware_searchdbal.dbal_query_builder_factory');
        $context = Shopware()->Container()->get('shopware_storefront.context_service')->getShopContext();

        $criteria = new Criteria();
        $criteria->addBaseCondition(new CategoryCondition([1]));
        $criteria->addCondition(new CategoryCondition([2]));

        /** @var \Shopware\Bundle\SearchBundleDBAL\QueryBuilder $query */
        $query = $queryFactory->createQuery($criteria, $context);

        static::assertTrue($query->hasState(CategoryConditionHandler::STATE_NAME));
        static::assertTrue($query->hasState(CategoryConditionHandler::STATE_NAME . '2'));
    }

    protected function getProduct(
        $number,
        ShopContext $context,
        Category $category = null,
        $additionally = null
    ) {
        return parent::getProduct($number, $context, $additionally);
    }

    /**
     * Override prevents a default category condition
     *
     * @param array $conditions
     */
    protected function addCategoryBaseCondition(
        Criteria $criteria,
        Category $category,
        $conditions,
        ShopContext $context
    ) {
    }
}

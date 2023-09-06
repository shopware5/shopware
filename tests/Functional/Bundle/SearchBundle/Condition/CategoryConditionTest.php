<?php

declare(strict_types=1);
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Tests\Functional\Bundle\SearchBundle\Condition;

use Shopware\Bundle\SearchBundle\Condition\CategoryCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundleDBAL\ConditionHandler\CategoryConditionHandler;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilderFactory;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Category\Category;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\TestCase;

/**
 * @group elasticSearch
 */
class CategoryConditionTest extends TestCase
{
    public function testMultipleCategories(): void
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
        $queryFactory = $this->getContainer()->get(QueryBuilderFactory::class);
        $context = $this->getContainer()->get(ContextServiceInterface::class)->getShopContext();

        $criteria = new Criteria();
        $criteria->addBaseCondition(new CategoryCondition([1]));
        $criteria->addCondition(new CategoryCondition([2]));

        $query = $queryFactory->createQuery($criteria, $context);

        static::assertTrue($query->hasState(CategoryConditionHandler::STATE_NAME));
        static::assertTrue($query->hasState(CategoryConditionHandler::STATE_NAME . '2'));
    }

    protected function getProduct(
        string $number,
        ShopContext $context,
        ?Category $category = null,
        $additionally = null
    ): array {
        if ($additionally !== null) {
            static::assertInstanceOf(Category::class, $additionally);
        }

        return parent::getProduct($number, $context, $additionally);
    }

    /**
     * Override prevents a default category condition
     */
    protected function addCategoryBaseCondition(Criteria $criteria, Category $category): void
    {
    }
}

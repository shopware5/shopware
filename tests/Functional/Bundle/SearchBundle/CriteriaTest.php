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

namespace Shopware\Tests\Functional\Bundle\SearchBundle;

use Shopware\Bundle\SearchBundle\Condition\CategoryCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\Facet\PriceFacet;
use Shopware\Bundle\SearchBundle\Sorting\PopularitySorting;
use Shopware\Bundle\SearchBundle\Sorting\PriceSorting;
use Shopware\Bundle\SearchBundle\Sorting\ProductNameSorting;
use Shopware\Bundle\SearchBundle\SortingInterface;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\TestCase;

class CriteriaTest extends TestCase
{
    public function testUniqueCondition()
    {
        $criteria = new Criteria();

        $criteria->addCondition(new CategoryCondition([1]));
        $criteria->addCondition(new CategoryCondition([3]));
        static::assertCount(1, $criteria->getConditions());
    }

    public function testUniqueFacet()
    {
        $criteria = new Criteria();
        $criteria->addFacet(new PriceFacet());
        $criteria->addFacet(new PriceFacet());
        static::assertCount(1, $criteria->getFacets());
    }

    public function testUniqueSorting()
    {
        $criteria = new Criteria();
        $criteria->addSorting(new PriceSorting());
        $criteria->addSorting(new PriceSorting());
        static::assertCount(1, $criteria->getSortings());
    }

    public function testIndexedSorting()
    {
        /** @var SortingInterface[] $sortings */
        $sortings = [
            new PriceSorting(),
            new ProductNameSorting(),
            new PopularitySorting(),
        ];

        $criteria = new Criteria();
        foreach ($sortings as $sort) {
            $criteria->addSorting($sort);
        }

        foreach ($sortings as $expected) {
            $sorting = $criteria->getSorting($expected->getName());
            static::assertEquals($expected, $sorting);
        }
    }

    public function testConditionOverwrite()
    {
        $criteria = new Criteria();

        $criteria->addCondition(new CategoryCondition([1]));

        $condition = new CategoryCondition([3]);
        $criteria->addCondition($condition);

        static::assertCount(1, $criteria->getConditions());
        $condition = $criteria->getCondition($condition->getName());

        static::assertInstanceOf('Shopware\Bundle\SearchBundle\Condition\CategoryCondition', $condition);

        /* @var CategoryCondition $condition */
        static::assertEquals([3], $condition->getCategoryIds());
    }
}

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

namespace Shopware\Tests\Functional\Bundle\ESIndexingBundle;

use Shopware\Bundle\SearchBundle\Condition\CategoryCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundleES\CombinedConditionQueryBuilder;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\TestCase;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\ShopContextTrait;

/**
 * @group elasticSearch
 */
class CombinedConditionQueryBuilderTest extends TestCase
{
    use ContainerTrait;
    use ShopContextTrait;

    public function testCombinedConditionQueryBuilder(): void
    {
        $combinedConditionQueryBuilder = $this->getCombinedConditionQueryBuilder();
        $shopContext = $this->createShopContext();
        $testCondition = new CategoryCondition([1]);

        $query = $combinedConditionQueryBuilder->build([
            $testCondition,
        ],
        new Criteria(),
        $shopContext
        );

        static::assertSame([
            'bool' => [
                'filter' => [
                    [
                        'terms' => [
                            'categoryIds' => [
                                1,
                            ],
                        ],
                    ],
                ],
                'must' => [
                    [
                        'bool' => [
                            'filter' => [
                                [
                                    'terms' => [
                                        'categoryIds' => [
                                            1,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ], $query->toArray());
    }

    public function getCombinedConditionQueryBuilder(): CombinedConditionQueryBuilder
    {
        return $this->getContainer()->get(CombinedConditionQueryBuilder::class);
    }
}

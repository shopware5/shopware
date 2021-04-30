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

use Shopware\Bundle\SearchBundle\Condition\OrdernumberCondition;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Category\Category;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\TestCase;

/**
 * @group elasticSearch
 */
class OrderNumberConditionTest extends TestCase
{
    public function testSingleMatch()
    {
        $condition = new OrdernumberCondition(['A1234567']);

        $this->search(
            [
                'A1234567' => 'A1234567',
                'A1234569' => 'A1234569',
                'A1234561' => 'A1234561',
            ],
            ['A1234567'],
            null,
            [$condition]
        );
    }

    public function createProducts($products, ShopContext $context, Category $category)
    {
        $articles = parent::createProducts($products, $context, $category);

        Shopware()->Container()->get(\Shopware\Bundle\SearchBundleDBAL\SearchTerm\SearchIndexer::class)->build();

        Shopware()->Container()->get(\Zend_Cache_Core::class)->clean('all', ['Shopware_Modules_Search']);

        return $articles;
    }

    /**
     * @param string $number
     * @param string $name
     *
     * @return array
     */
    protected function getProduct(
        $number,
        ShopContext $context,
        Category $category = null,
        $name = null
    ) {
        return parent::getProduct($number, $context, $category);
    }
}

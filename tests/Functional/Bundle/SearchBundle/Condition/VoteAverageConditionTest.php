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

use Shopware\Bundle\SearchBundle\Condition\VoteAverageCondition;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Category\Category;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\TestCase;

class VoteAverageConditionTest extends TestCase
{
    public function testVoteAverageCondition()
    {
        $condition = new VoteAverageCondition(3);

        $this->search(
            [
                'first' => [1, 2],
                'second' => [4, 5],
                'third' => [3, 5],
                'fourth' => [3, 3],
                'first-2' => [1, 2],
                'second-2' => [4, 5],
                'third-2' => [3, 5],
            ],
            ['second', 'third', 'fourth', 'second-2', 'third-2'],
            null,
            [$condition]
        );
    }

    protected function createProduct(
        $number,
        ShopContext $context,
        Category $category,
        $additionally
    ) {
        $article = parent::createProduct(
            $number,
            $context,
            $category,
            $additionally
        );

        $this->helper->createVotes($article->getId(), $additionally);

        return $article;
    }
}

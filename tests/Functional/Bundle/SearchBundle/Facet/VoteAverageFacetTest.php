<?php

declare(strict_types=1);
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

namespace Shopware\Tests\Functional\Bundle\SearchBundle\Facet;

use Shopware\Bundle\SearchBundle\Facet\VoteAverageFacet;
use Shopware\Bundle\SearchBundle\FacetResult\RadioFacetResult;
use Shopware\Bundle\StoreFrontBundle\Struct\Shop;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Article;
use Shopware\Models\Category\Category;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\TestCase;

/**
 * @group elasticSearch
 */
class VoteAverageFacetTest extends TestCase
{
    public function testVoteAverageFacet(): void
    {
        $context = $this->getContext();

        $result = $this->search(
            [
                'first' => [
                    1 => [1, 2],     //shop = 1    1x vote with 1 point    1x vote with 2 points
                ],
                'second' => [
                    1 => [4, 5],
                ],
                'third' => [
                    1 => [3, 5],
                ],
                'first-2' => [
                    1 => [1, 2],
                ],
                'second-2' => [
                    1 => [4, 5],
                ],
                'third-2' => [
                    1 => [3, 5],
                ],
            ],
            ['first', 'second', 'third', 'first-2', 'second-2', 'third-2'],
            null,
            [],
            [new VoteAverageFacet()],
            [],
            $context
        );

        static::assertInstanceOf(RadioFacetResult::class, $result->getFacets()[0]);
    }

    public function testVoteFacetWithoutSubshopVotes(): void
    {
        $context = $this->getContext(2);

        $result = $this->search(
            [
                'first' => [
                    1 => [1, 2],     //shop = 1    1x vote with 1 point    1x vote with 2 points
                ],
            ],
            ['first'],
            $this->createCategory($context->getShop()),
            [],
            [new VoteAverageFacet()],
            [],
            $context,
            ['displayOnlySubShopVotes' => true]
        );

        foreach ($result->getFacets() as $facet) {
            static::assertNotInstanceOf(VoteAverageFacet::class, $facet);
        }
    }

    public function testVoteFacetWithSubshopVotes(): void
    {
        $context = $this->getContext(2);

        $result = $this->search(
            [
                'first' => [
                    2 => [1, 2],     //shop = 1    1x vote with 1 point    1x vote with 2 points
                ],
            ],
            ['first'],
            $this->createCategory($context->getShop()),
            [],
            [new VoteAverageFacet()],
            [],
            $context,
            ['displayOnlySubShopVotes' => true]
        );

        static::assertInstanceOf(RadioFacetResult::class, $result->getFacets()[0]);
    }

    public function testVoteFacetWithNotAssignedSubShop(): void
    {
        $context = $this->getContext(2);

        $result = $this->search(
            [
                'first' => [
                    null => [1, 2],      //shop = 1    1x vote with 1 point    1x vote with 2 points
                    1 => [1, 2],         //shop = null    1x vote with 1 point    1x vote with 2 points
                ],
            ],
            ['first'],
            $this->createCategory($context->getShop()),
            [],
            [new VoteAverageFacet()],
            [],
            $context,
            ['displayOnlySubShopVotes' => true]
        );

        static::assertInstanceOf(RadioFacetResult::class, $result->getFacets()[0]);
    }

    /**
     * @param array<string, array<int>> $additionally
     */
    protected function createProduct(
        string $number,
        ShopContext $context,
        Category $category,
        $additionally
    ): Article {
        $article = parent::createProduct(
            $number,
            $context,
            $category,
            $additionally
        );

        foreach ($additionally as $shopId => $votes) {
            if (empty($shopId)) {
                $shopId = null;
            } else {
                $shopId = (int) $shopId;
            }
            $this->helper->createVotes($article->getId(), $votes, $shopId);
        }

        return $article;
    }

    private function createCategory(Shop $shop): Category
    {
        $category = Shopware()->Container()->get(ModelManager::class)
            ->find(Category::class, $shop->getCategory()->getId());

        return $this->helper->createCategory(['parent' => $category]);
    }
}

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

namespace Shopware\Tests\Functional\Bundle\StoreFrontBundle;

use Shopware\Bundle\StoreFrontBundle\Service\ListProductServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\VoteServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\VoteAverage;
use Shopware_Components_Config;

class VoteTest extends TestCase
{
    public function testVoteList(): void
    {
        $number = 'testVoteList';
        $context = $this->getContext();
        $data = $this->getProduct($number, $context);
        $product = $this->helper->createProduct($data);

        $points = [1, 2, 2, 3, 3];
        $this->helper->createVotes($product->getId(), $points);

        $listProduct = Shopware()->Container()->get(ListProductServiceInterface::class)->get($number, $context);
        static::assertNotNull($listProduct);
        $votes = Shopware()->Container()->get(VoteServiceInterface::class)->get($listProduct, $context);
        static::assertIsArray($votes);

        static::assertCount(5, $votes);

        foreach ($votes as $vote) {
            static::assertEquals('Bert Bewerter', $vote->getName());
        }
    }

    public function testVoteAverage(): void
    {
        $number = 'testVoteAverage';
        $context = $this->getContext();
        $data = $this->getProduct($number, $context);
        $product = $this->helper->createProduct($data);

        $points = [1, 2, 2, 3, 3, 3, 3, 3];
        $this->helper->createVotes($product->getId(), $points);

        $listProduct = Shopware()->Container()->get(ListProductServiceInterface::class)->get($number, $context);
        static::assertNotNull($listProduct);
        $voteAverage = $listProduct->getVoteAverage();
        static::assertNotNull($voteAverage);

        static::assertEquals(5, $voteAverage->getAverage());

        foreach ($voteAverage->getPointCount() as $pointCount) {
            switch ($pointCount['points']) {
                case 1:
                    static::assertEquals(1, $pointCount['total']);
                    break;
                case 2:
                    static::assertEquals(2, $pointCount['total']);
                    break;
                case 3:
                    static::assertEquals(5, $pointCount['total']);
                    break;
            }
        }
    }

    public function testAverage(): void
    {
        $this->assertShopVotes(
            __FUNCTION__,
            // generate 2x vote entries for shop "1" with points 1 and 5
            [1 => [1, 5]],

            // expects shop "1" has a count of 3 votes and an average of 6
            [1 => ['count' => 2, 'average' => 6]]
        );
    }

    public function testSimpleShopVotes(): void
    {
        $this->assertShopVotes(
            __FUNCTION__,
            // generate 3x vote entries for shop "1" with points 3,4,5
            [1 => [3, 4, 5]],

            // expects shop "1" has a count of 3 votes and an average of 8 (average*2)
            [1 => ['count' => 3, 'average' => 8, 'points' => [3 => 1, 4 => 1, 5 => 1]]]
        );
    }

    public function testShopVotes(): void
    {
        $this->assertShopVotes(
            __FUNCTION__,
            // generate 3x vote entries for shop "1" with points 3,4,5
            [1 => [3, 4, 5],           2 => [4, 4, 4]],

            // expects shop "1" has a count of 3 votes and an average of 8 (average*2)
            [
                1 => ['count' => 3, 'average' => 8, 'points' => [3 => 1, 4 => 1, 5 => 1]],
                2 => ['count' => 3, 'average' => 8, 'points' => [4 => 3]],
            ],
            ['displayOnlySubShopVotes' => true]
        );
    }

    public function testLegacyVotes(): void
    {
        $this->assertShopVotes(
            __FUNCTION__,
            // generate 3x vote entries for shop "null" with points 3,4,5 and shop "1" three times with 4 points
            [
                null => [3, 4, 5],
                1 => [4, 4, 4],
            ],

            // expects shop "1" has a count of 6 votes and an average of 8 (average*2)
            [
                1 => ['count' => 6, 'average' => 8, 'points' => [3 => 1, 4 => 4, 5 => 1]],
                2 => ['count' => 3, 'average' => 8, 'points' => [3 => 1, 4 => 1, 5 => 1]],
            ],
            ['displayOnlySubShopVotes' => true]
        );
    }

    public function testMixedVotes(): void
    {
        $this->assertShopVotes(
            __FUNCTION__,
            [
                null => [3],
                1 => [4],
                2 => [5, 4],
            ],
            [
                1 => ['count' => 2, 'average' => 7, 'points' => [3 => 1, 4 => 1]],
                2 => ['count' => 3, 'average' => 8, 'points' => [3 => 1, 4 => 1, 5 => 1]],
            ],
            ['displayOnlySubShopVotes' => true]
        );
    }

    public function testDisabledConfig(): void
    {
        $this->assertShopVotes(
            __FUNCTION__,
            [
                null => [3],
                1 => [4],
                2 => [5],
            ],
            [
                1 => ['count' => 3, 'average' => 8, 'points' => [3 => 1, 4 => 1, 5 => 1]],
                2 => ['count' => 3, 'average' => 8, 'points' => [3 => 1, 4 => 1, 5 => 1]],
            ],
            ['displayOnlySubShopVotes' => false]
        );
    }

    /**
     * @param array<array<int>>                                                     $points
     * @param array<int, array{count: int, average: int, points?: array<int, int>}> $expected
     * @param array<string, bool>                                                   $configs
     */
    private function assertShopVotes(string $number, array $points = [], array $expected = [], array $configs = []): void
    {
        // switch config values
        $config = Shopware()->Container()->get(Shopware_Components_Config::class);
        $originals = [];
        foreach ($configs as $key => $value) {
            $originals = $config->get($key);
            $config->offsetSet($key, $value);
        }

        // generate simple product
        $context = $this->getContext();
        $data = $this->getProduct($number, $context);
        $product = $this->helper->createProduct($data);

        // generate shop specified votes generated
        foreach ($points as $shopId => $shopPoints) {
            // fix for shop id = null as array key
            if (!\is_int($shopId)) {
                $shopId = null;
            }
            $this->helper->createVotes($product->getId(), $shopPoints, $shopId);
        }

        // load product struct
        $factory = Shopware()->Container()->get('shopware_storefront.base_product_factory');
        $product = $factory->createBaseProduct($number);
        static::assertInstanceOf(BaseProduct::class, $product);
        $service = Shopware()->Container()->get(VoteServiceInterface::class);

        // iterate all expected shop votes/averages
        foreach ($expected as $shopId => $data) {
            $context = $this->getContext($shopId);

            // validate vote count of provided shop
            if (\array_key_exists('count', $data)) {
                $votes = $service->get($product, $context);
                static::assertIsArray($votes);
                static::assertCount($data['count'], $votes, sprintf('Vote count %s for shop %s of product %s not match', $data['count'], $shopId, $product->getNumber()));
            }

            // validates provided average value of provided shop
            if (\array_key_exists('average', $data)) {
                $average = $service->getAverage($product, $context);
                static::assertInstanceOf(VoteAverage::class, $average);
                static::assertEquals($data['average'], $average->getAverage(), sprintf('Vote average %s for shop %s of product %s not match', $data['average'], $shopId, $product->getNumber()));
            }

            if (\array_key_exists('points', $data)) {
                $average = $service->getAverage($product, $context);
                static::assertInstanceOf(VoteAverage::class, $average);

                $actual = [];
                foreach ($average->getPointCount() as $row) {
                    $actual[$row['points']] = $row['total'];
                }

                foreach ($data['points'] as $point => $count) {
                    static::assertArrayHasKey($point, $actual, sprintf('Point count for points %s not exist', $point));
                    static::assertEquals($count, $actual[$point], sprintf('Expected %s times votes with points %s', $count, $point));
                }
            }
        }

        // reset config values
        foreach ($originals as $key => $value) {
            $config->offsetSet($key, $value);
        }
    }
}

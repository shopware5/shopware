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

namespace Shopware\Tests\Functional\Bundle\StoreFrontBundle;

use Shopware\Bundle\StoreFrontBundle\Struct\Product\Vote;

class VoteTest extends TestCase
{
    public function testVoteList()
    {
        $number = 'testVoteList';
        $context = $this->getContext();
        $data = $this->getProduct($number, $context);
        $product = $this->helper->createArticle($data);

        $points = [1, 2, 2, 3, 3];
        $this->helper->createVotes($product->getId(), $points);

        $listProduct = Shopware()->Container()->get('shopware_storefront.list_product_service')->get($number, $context);
        $votes = Shopware()->Container()->get('shopware_storefront.vote_service')->get($listProduct, $context);

        $this->assertCount(5, $votes);

        /** @var $vote Vote */
        foreach ($votes as $vote) {
            $this->assertEquals('Bert Bewerter', $vote->getName());
        }
    }

    public function testVoteAverage()
    {
        $number = 'testVoteAverage';
        $context = $this->getContext();
        $data = $this->getProduct($number, $context);
        $product = $this->helper->createArticle($data);

        $points = [1, 2, 2, 3, 3, 3, 3, 3];
        $this->helper->createVotes($product->getId(), $points);

        $listProduct = Shopware()->Container()->get('shopware_storefront.list_product_service')->get($number, $context);
        $voteAverage = Shopware()->Container()->get('shopware_storefront.vote_service')->getAverage($listProduct, $context);

        $this->assertEquals(5, $voteAverage->getAverage());

        foreach ($voteAverage->getPointCount() as $pointCount) {
            switch ($pointCount['points']) {
                case 1:
                    $this->assertEquals(1, $pointCount['total']);
                    break;
                case 2:
                    $this->assertEquals(2, $pointCount['total']);
                    break;
                case 3:
                    $this->assertEquals(5, $pointCount['total']);
                    break;
            }
        }
    }
}

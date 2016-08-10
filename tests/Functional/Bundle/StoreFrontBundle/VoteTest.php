<?php

namespace Shopware\Tests\Bundle\StoreFrontBundle;

use Shopware\Bundle\StoreFrontBundle\Struct\Product\Vote;


class VoteTest extends TestCase
{
    public function testVoteList()
    {
        $number = 'testVoteList';
        $context = $this->getContext();
        $data = $this->getProduct($number, $context);
        $product = $this->helper->createArticle($data);

        $points = array(1,2,2,3,3);
        $this->helper->createVotes($product->getId(), $points);

        $listProduct = Shopware()->Container()->get('shopware_storefront.list_product_service')->get($number, $context);
        $votes = Shopware()->Container()->get('shopware_storefront.vote_service')->get($listProduct, $context);

        $this->assertCount(5, $votes);

        /**@var $vote Vote*/
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

        $points = array(1,2,2,3,3,3,3,3);
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

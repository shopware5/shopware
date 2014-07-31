<?php

namespace Shopware\Tests\Service\Product;

use Shopware\Bundle\StoreFrontBundle\Struct\Context;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\Vote;
use Shopware\Models\Article\Article;
use Shopware\Models\Category\Category;
use Shopware\Tests\Service\Converter;
use Shopware\Tests\Service\Helper;

class VoteTest extends \Enlight_Components_Test_TestCase
{
    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var Converter
     */
    private $converter;

    protected function setUp()
    {
        $this->helper = new Helper();
        $this->converter = new Converter();

        parent::setUp();
    }

    protected function tearDown()
    {
        $this->helper->cleanUp();
        parent::tearDown();
    }


    private function createVotes($articleId, $points = array())
    {
        $data = array(
            'id' => null,
            'articleID' => $articleId,
            'name' => 'Bert Bewerter',
            'headline' => 'Super Artikel',
            'comment' => 'Dieser Artikel zeichnet sich durch extreme Stabilität aus und fasst super viele Klamotten. Das Preisleistungsverhältnis ist exorbitant gut.',
            'points' => '5',
            'datum' => '2012-08-29 14:02:24',
            'active' => '1'
        );

        foreach($points as $point) {
            $data['points'] = $point;

            Shopware()->Db()->insert('s_articles_vote', $data);
        }
    }

    /**
     * @param $number
     * @param Context $context
     * @return Article
     */
    private function getDefaultProduct($number, Context $context)
    {
        $product = $this->helper->getSimpleProduct(
            $number,
            array_shift($context->getTaxRules()),
            $context->getCurrentCustomerGroup()
        );

        return $this->helper->createArticle($product);
    }

    /**
     * @return Context
     */
    private function getContext()
    {
        $tax = $this->helper->createTax();
        $customerGroup = $this->helper->createCustomerGroup();
        $shop = $this->helper->getShop();

        return $this->helper->createContext(
            $customerGroup,
            $shop,
            array($tax)
        );
    }

    public function testVoteList()
    {
        $number = 'testVoteList';
        $context = $this->getContext();
        $product = $this->getDefaultProduct($number, $context);

        $points = array(1,2,2,3,3);
        $this->createVotes($product->getId(), $points);

        $listProduct = Shopware()->Container()->get('list_product_service_core')->get($number, $context);
        $votes = Shopware()->Container()->get('vote_service_core')->get($listProduct, $context);

        $this->assertCount(5, $votes);

        $points = array_reverse($points);

        /**@var $vote Vote*/
        foreach($votes as $index => $vote) {
            $this->assertEquals('Bert Bewerter', $vote->getName());
            $this->assertEquals($points[$index], $vote->getPoints());
        }
    }


    public function testVoteAverage()
    {
        $number = 'testVoteAverage';
        $context = $this->getContext();
        $product = $this->getDefaultProduct($number, $context);

        $points = array(1,2,2,3,3,3,3,3);
        $this->createVotes($product->getId(), $points);

        $listProduct = Shopware()->Container()->get('list_product_service_core')->get($number, $context);
        $voteAverage = Shopware()->Container()->get('vote_service_core')->getAverage($listProduct, $context);

        $this->assertEquals(5, $voteAverage->getAverage());

        foreach($voteAverage->getPointCount() as $pointCount) {
            switch($pointCount['points']) {
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

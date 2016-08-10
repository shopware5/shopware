<?php

namespace Shopware\Tests\Bundle\SearchBundle\Facet;

use Shopware\Bundle\SearchBundle\Facet\VoteAverageFacet;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Category\Category;
use Shopware\Tests\Bundle\StoreFrontBundle\TestCase;

class VoteAverageFacetTest extends TestCase
{
    public function testVoteAverageFacet()
    {
        $result = $this->search(
            array(
                'first'  => array(1, 2),
                'second' => array(4, 5),
                'third'  => array(3, 5),
                'first-2'  => array(1, 2),
                'second-2' => array(4, 5),
                'third-2'  => array(3, 5)
            ),
            array('first', 'second', 'third', 'first-2', 'second-2', 'third-2'),
            null,
            array(),
            array(new VoteAverageFacet())
        );

        $this->assertInstanceOf('Shopware\Bundle\SearchBundle\FacetResult\RadioFacetResult', $result->getFacets()[0]);
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

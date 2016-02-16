<?php

namespace Shopware\Tests\Service\Search\Condition;

use Shopware\Bundle\SearchBundle\Condition\VoteAverageCondition;
use Shopware\Bundle\StoreFrontBundle\Struct\ProductContext;
use Shopware\Models\Category\Category;
use Shopware\Tests\Service\TestCase;

class VoteAverageConditionTest extends TestCase
{
    public function testVoteAverageCondition()
    {
        $condition = new VoteAverageCondition(3);

        $this->search(
            array(
                'first'  => array(1, 2),
                'second' => array(4, 5),
                'third'  => array(3, 5),
                'fourth'  => array(3, 3),
                'first-2'  => array(1, 2),
                'second-2' => array(4, 5),
                'third-2'  => array(3, 5)
            ),
            array('second', 'third', 'fourth', 'second-2', 'third-2'),
            null,
            array($condition)
        );
    }

    protected function createProduct(
        $number,
        ProductContext $context,
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

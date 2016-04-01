<?php

namespace Shopware\Tests\Service\Search\Condition;

use Shopware\Bundle\SearchBundle\Condition\SimilarProductCondition;
use Shopware\Bundle\StoreFrontBundle\Struct\ProductContext;
use Shopware\Models\Category\Category;
use Shopware\Tests\Service\TestCase;

class SimilarProductConditionTest extends TestCase
{
    protected function getProduct(
        $number,
        ProductContext $context,
        Category $category = null,
        $additionally = null
    ) {
        return parent::getProduct($number, $context, $additionally);
    }

    public function testSimilarProduct()
    {
        $main = $this->helper->createCategory(['name' => 'main']);
        $first = $this->helper->createCategory(['name' => 'first-category', 'parent' => $main]);
        $second = $this->helper->createCategory(['name' => 'second-category', 'parent' => $main]);

        $product = $this->getProduct('test', $this->getContext(), null, $second);
        $article = $this->helper->createArticle($product);
        $condition = new SimilarProductCondition($article->getId(), $article->getName());

        $this->search([
            'one' => $first,
            'two' => $first,
            'three' => $first,
            'four' => $first,
            'five' => $first,
            'six' => $second,
            'seven' => $second,
            'eight' => $second,
            'nine' => $second,
            'ten' => $second,
        ],
            ['six', 'seven', 'eight', 'nine', 'ten'],
            $main,
            [$condition]
        );
    }
}

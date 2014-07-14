<?php

namespace Shopware\Tests\Service\Search;

use Shopware\Bundle\SearchBundle\ProductNumberSearchResult;

class TestCase extends \Enlight_Components_Test_TestCase
{
    protected function assertSearchResult(ProductNumberSearchResult $result, $expectedNumbers)
    {
        $this->assertCount(count($expectedNumbers), $result->getProducts());
        $this->assertEquals(count($expectedNumbers), $result->getTotalCount());

        foreach ($result->getProducts() as $product) {
            $this->assertContains(
                $product->getNumber(),
                $expectedNumbers
            );
        }
    }
}

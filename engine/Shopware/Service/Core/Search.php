<?php

namespace Shopware\Service\Core;

use Shopware\Struct;
use Shopware\Service;
use Shopware\Gateway;

class Search implements Service\Search
{
    /**
     * @var Gateway\Search
     */
    private $searchGateway;

    /**
     * @var Service\ListProduct
     */
    private $productService;

    /**
     * @param Service\ListProduct $productService
     * @param Gateway\Search $searchGateway
     */
    function __construct(
        Service\ListProduct $productService,
        Gateway\Search $searchGateway
    ) {
        $this->productService = $productService;
        $this->searchGateway = $searchGateway;
    }

    /**
     * @inheritdoc
     */
    public function search(Gateway\Search\Criteria $criteria, Context $context)
    {
        $result = $this->searchGateway->search(
            $criteria,
            $context
        );

        $numbers = $this->getNumbers($result);

        $products = $this->productService->getList(
            $numbers,
            $context
        );

        $products = $this->assignAttributes(
            $products,
            $result->getProducts()
        );

        return new Gateway\Search\Result(
            $products,
            $result->getTotalCount(),
            $result->getFacets()
        );
    }


    /**
     * @param Struct\ListProduct[] $products
     * @param \Shopware\Gateway\Search\Product[] $searchProducts
     * @return Struct\ListProduct[]
     */
    private function assignAttributes($products, $searchProducts)
    {
        foreach ($searchProducts as $searchProduct) {
            $number = $searchProduct->getNumber();

            $product = $products[$number];

            if (!$product) {
                continue;
            }

            foreach ($searchProduct->getAttributes() as $key => $attribute) {
                $product->addAttribute($key, $attribute);
            }
        }

        return $products;
    }

    /**
     * @param Gateway\Search\Result $result
     * @return array
     */
    private function getNumbers(Gateway\Search\Result $result)
    {
        $numbers = array();
        foreach ($result->getProducts() as $product) {
            $numbers[] = $product->getNumber();
        }

        return $numbers;
    }
}

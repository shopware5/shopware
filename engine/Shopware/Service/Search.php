<?php

namespace Shopware\Service;

use Shopware\Gateway\DBAL as Gateway;
use Shopware\Gateway\Search\Product;
use Shopware\Gateway\Search\Result;
use Shopware\Struct;

class Search
{
    /**
     * @var Gateway\Search
     */
    private $searchGateway;

    /**
     * @var ListProduct
     */
    private $productService;

    /**
     * @param ListProduct $productService
     * @param Gateway\Search $searchGateway
     */
    function __construct(ListProduct $productService, Gateway\Search $searchGateway)
    {
        $this->productService = $productService;
        $this->searchGateway = $searchGateway;
    }

    /**
     * Creates a search request on the internal search gateway to
     * get the product result for the passed criteria object.
     *
     * @param Criteria $criteria
     * @param Context $context
     * @return \Shopware\Gateway\Search\Result
     */
    public function search(Criteria $criteria, Context $context)
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

        return new Result(
            $products,
            $result->getTotalCount(),
            $result->getFacets()
        );
    }


    /**
     * @param Struct\ListProduct[] $products
     * @param Product[] $searchProducts
     * @return mixed
     */
    private function assignAttributes($products, $searchProducts)
    {
        foreach($searchProducts as $searchProduct) {
            $number = $searchProduct->getNumber();

            $product = $products[$number];

            if (!$product) {
                continue;
            }

            foreach($searchProduct->getAttributes() as $key => $attribute) {
                $product->addAttribute($key, $attribute);
            }
        }

        return $products;
    }

    /**
     * @param Result $result
     * @return array
     */
    private function getNumbers(Result $result)
    {
        $numbers = array();
        foreach ($result->getProducts() as $product) {
            $numbers[] = $product->getNumber();
        }

        return $numbers;
    }
}
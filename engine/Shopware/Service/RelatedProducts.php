<?php

namespace Shopware\Service;

use Shopware\Gateway\DBAL as Gateway;
use Shopware\Struct;

class RelatedProducts
{
    /**
     * @var Gateway\RelatedProducts
     */
    private $gateway;

    /**
     * @var ListProduct
     */
    private $listProductService;

    function __construct(
        Gateway\RelatedProducts $gateway,
        ListProduct $listProductService
    ) {
        $this->gateway = $gateway;
        $this->listProductService = $listProductService;
    }

    /**
     * @param Struct\ListProduct[] $products
     * @param Struct\Context $context
     * @return array Indexed with the product number, the values are a list of ListProduct structs.
     */
    public function getList(array $products, Struct\Context $context)
    {
        /**
         * returns an array which is associated with the different product numbers.
         * Each array contains a list of product numbers which are related to the reference product.
         */
        $numbers = $this->gateway->getList($products);

        //loads the list product data for the selected numbers.
        //all numbers are joined in the extractNumbers function to prevent that a product will be
        //loaded multiple times
        $listProducts = $this->listProductService->getList(
            $this->extractNumbers($numbers),
            $context
        );

        $result = array();
        foreach ($products as $product) {
            $result[$product->getNumber()] = $this->getProductsByNumbers(
                $listProducts,
                $numbers[$product->getId()]
            );
        }

        return $result;
    }

    /**
     * @param Struct\ListProduct[] $products
     * @param array $numbers
     * @return Struct\ListProduct[]
     */
    private function getProductsByNumbers(array $products, array $numbers)
    {
        $result = array();

        foreach ($products as $product) {
            if (in_array($product->getNumber(), $numbers)) {
                $result[] = $product;
            }
        }
        return $result;
    }

    /**
     * @param $numbers
     * @return array
     */
    private function extractNumbers($numbers)
    {
        //collect all numbers to send a single list product request.
        $related = array();
        foreach ($numbers as $value) {
            $related = array_merge($related, $value);
        }

        //filter duplicate numbers to prevent duplicate data requests and iterations.
        $unique = array_unique($related);
        return array_values($unique);
    }
}

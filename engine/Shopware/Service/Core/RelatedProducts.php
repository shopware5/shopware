<?php

namespace Shopware\Service\Core;

use Shopware\Struct;
use Shopware\Service;
use Shopware\Gateway;

class RelatedProducts implements Service\RelatedProducts
{
    /**
     * @var Gateway\RelatedProducts
     */
    private $gateway;

    /**
     * @var Service\ListProduct
     */
    private $listProductService;

    /**
     * @param Gateway\RelatedProducts $gateway
     * @param Service\ListProduct $listProductService
     */
    function __construct(
        Gateway\RelatedProducts $gateway,
        Service\ListProduct $listProductService
    ) {
        $this->gateway = $gateway;
        $this->listProductService = $listProductService;
    }

    /**
     * Selects all related products for the provided product.
     *
     * The relation between the products are selected over the \Shopware\Gateway\RelatedProducts class.
     * After the relation is selected, the \Shopware\Service\ListProduct is used to load
     * the whole product data for the relations.
     *
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Service\ListProduct::get()
     *
     * @param Struct\ListProduct $product
     * @param Struct\Context $context
     * @return Struct\ListProduct[] Indexed by the product order number.
     */
    public function get(Struct\ListProduct $product, Struct\Context $context)
    {
        $related = $this->getList(array($product), $context);
        return array_shift($related);
    }

    /**
     * @see \Shopware\Service\RelatedProducts::get()
     *
     * @param Struct\ListProduct[] $products
     * @param Struct\Context $context
     * @return array Indexed with the product number, each array element contains a \Shopware\Struct\ListProduct array.
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
                $result[$product->getNumber()] = $product;
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

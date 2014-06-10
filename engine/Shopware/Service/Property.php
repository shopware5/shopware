<?php

namespace Shopware\Service;

use Shopware\Struct as Struct;
use Shopware\Gateway\DBAL as Gateway;

/**
 * @package Shopware\Service
 */
class Property
{
    /**
     * @var \Shopware\Gateway\DBAL\ProductProperty
     */
    private $productPropertyGateway;

    /**
     * @param Gateway\ProductProperty $productPropertyGateway
     */
    function __construct(Gateway\ProductProperty $productPropertyGateway)
    {
        $this->productPropertyGateway = $productPropertyGateway;
    }

    /**
     * @param Struct\ListProduct[] $products
     * @param Struct\Context $context
     * @return Struct\Property\Set[]
     */
    public function getList(array $products, Struct\Context $context)
    {
        $properties = $this->productPropertyGateway->getList($products, $context);

        return $properties;
    }

    /**
     * Returns a single \Struct\Property\Set for the passed value id.
     *
     * @param \Shopware\Struct\ListProduct $product
     * @param Struct\Context $context
     * @return Struct\Property\Set
     */
    public function get(Struct\ListProduct $product, Struct\Context $context)
    {
        $properties = $this->getList(array($product), $context);

        return array_shift($properties);
    }
}
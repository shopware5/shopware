<?php

namespace Shopware\Service\Core;

use Shopware\Struct;
use Shopware\Service;
use Shopware\Gateway;

/**
 * @package Shopware\Service
 */
class Property implements Service\Property
{
    /**
     * @var Gateway\ProductProperty
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
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Gateway\ProductProperty::get()
     *
     * @param Struct\ListProduct $product
     * @param Struct\Context $context
     * @return Struct\Property\Set
     */
    public function get(Struct\ListProduct $product, Struct\Context $context)
    {
        $properties = $this->getList(array($product), $context);

        return array_shift($properties);
    }

    /**
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Service\Property::get()
     *
     * @param Struct\ListProduct[] $products
     * @param Struct\Context $context
     * @return Struct\Property\Set[]
     */
    public function getList(array $products, Struct\Context $context)
    {
        $properties = $this->productPropertyGateway->getList($products, $context);

        return $properties;
    }
}

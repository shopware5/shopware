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
     * @inheritdoc
     */
    public function get(Struct\ListProduct $product, Struct\Context $context)
    {
        $properties = $this->getList(array($product), $context);

        return array_shift($properties);
    }

    /**
     * @inheritdoc
     */
    public function getList(array $products, Struct\Context $context)
    {
        $properties = $this->productPropertyGateway->getList($products, $context);

        return $properties;
    }
}

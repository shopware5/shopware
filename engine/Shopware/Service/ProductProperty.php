<?php

namespace Shopware\Service;

use Shopware\Struct as Struct;
use Shopware\Gateway\DBAL as Gateway;

/**
 * @package Shopware\Service
 */
class ProductProperty
{
    /**
     * @var \Shopware\Gateway\DBAL\ProductProperty
     */
    private $productPropertyGateway;

    /**
     * @var Translation
     */
    private $translationService;

    /**
     * @param Gateway\ProductProperty $productPropertyGateway
     * @param Translation $translationService
     */
    function __construct(
        Gateway\ProductProperty $productPropertyGateway,
        Translation $translationService
    ) {
        $this->productPropertyGateway = $productPropertyGateway;
        $this->translationService = $translationService;
    }

    /**
     * @param Struct\ListProduct[] $products
     * @param Struct\Context $context
     * @return Struct\Property\Set[]
     */
    public function getList(array $products, Struct\Context $context)
    {
        $properties = $this->productPropertyGateway->getList($products);

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
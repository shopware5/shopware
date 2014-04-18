<?php

namespace Shopware\Service;

use Shopware\Struct as Struct;
use Shopware\Gateway as Gateway;

/**
 * @package Shopware\Service
 */
class Property
{
    /**
     * @var \Shopware\Gateway\Property
     */
    private $propertyGateway;

    /**
     * @var Translation
     */
    private $translationService;

    function __construct(Gateway\Property $propertyGateway, Translation $translationService)
    {
        $this->propertyGateway = $propertyGateway;
        $this->translationService = $translationService;
    }

    /**
     * @param \Shopware\Struct\ListProduct $product
     * @param \Shopware\Struct\Context $context
     *
     * @return array|\Shopware\Struct\Property\Set
     */
    public function getProductProperty(Struct\ListProduct $product, Struct\Context $context)
    {
        $set = $this->propertyGateway->getProductSet($product);

        if (!$set) {
            return null;
        }

        $this->translationService->translatePropertySet($set, $context->getShop());

        return $set;
    }
}
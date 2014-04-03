<?php

namespace Shopware\Service;

use Shopware\Struct as Struct;
use Shopware\Gateway\ORM as Gateway;

/**
 * @package Shopware\Service
 */
class Property
{
    /**
     * @var \Shopware\Gateway\ORM\Property
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
     * @param \Shopware\Struct\ProductMini $product
     * @param \Shopware\Struct\GlobalState $state
     *
     * @return array|\Shopware\Struct\PropertySet
     */
    public function getProductProperty(Struct\ProductMini $product, Struct\GlobalState $state)
    {
        $set = $this->propertyGateway->getProductSet($product);

        $this->translationService->translatePropertySet($set, $state->getShop());

        echo '<pre>';
        \Doctrine\Common\Util\Debug::dump($set, 22);
        exit();

        return $set;
    }
}
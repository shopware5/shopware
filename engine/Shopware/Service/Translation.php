<?php

namespace Shopware\Service;

use Shopware\Struct as Struct;
use Shopware\Gateway as Gateway;

class Translation
{
    /**
     * @var \Shopware\Gateway\Translation
     */
    private $translationGateway;

    /**
     * @param Gateway\Translation $translationGateway
     */
    function __construct(Gateway\Translation $translationGateway)
    {
        $this->translationGateway = $translationGateway;
    }

    /**
     * @param \Shopware\Struct\ProductMini $product
     * @param \Shopware\Struct\Shop $shop
     */
    public function translateProduct(Struct\ProductMini $product, Struct\Shop $shop)
    {

        $this->translationGateway->translateProduct(
            $product,
            $shop
        );

        if ($product->getUnit()) {
            $this->translationGateway->translateUnit(
                $product->getUnit(),
                $shop
            );
        }

        if ($product->getManufacturer()) {
            $this->translationGateway->translateManufacturer(
                $product->getManufacturer(),
                $shop
            );
        }

        if ($product->getCheapestPrice() && $product->getCheapestPrice()->getUnit()) {
            $this->translationGateway->translateUnit(
                $product->getCheapestPrice()->getUnit(),
                $shop
            );
        }

        $product->addState(
            Struct\ProductMini::STATE_TRANSLATED
        );
    }

    public function translatePropertySet(Struct\PropertySet $set, Struct\Shop $shop)
    {
        $this->translationGateway->translatePropertySet(
            $set, $shop
        );
    }
}
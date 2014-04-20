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
     * @param \Shopware\Struct\ListProduct $product
     * @param \Shopware\Struct\Shop $shop
     */
    public function translateProduct(Struct\ListProduct $product, Struct\Shop $shop)
    {
        $translation = $this->translationGateway->translateProduct(
            $product,
            $shop
        );

        if ($translation) {
            // do translate
        }


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
            Struct\ListProduct::STATE_TRANSLATED
        );
    }

    public function translatePropertySet(Struct\PropertySet $set, Struct\Shop $shop)
    {
        $this->translationGateway->translatePropertySet(
            $set,
            $shop
        );
    }
}
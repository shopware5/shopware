<?php

namespace Shopware\Service;

use Shopware\Struct as Struct;
use Shopware\Gateway\ORM as Gateway;

class Product
{
    /**
     * @var Gateway\Product
     */
    private $productGateway;

    /**
     * @var GlobalState
     */
    private $globalStateService;

    /**
     * @var Price
     */
    private $priceService;

    /**
     * @var Translation
     */
    private $translationService;

    /**
     * @var \Enlight_Event_EventManager
     */
    private $eventManager;

    /**
     * @param Gateway\Product $productGateway
     * @param Price $priceService
     * @param Translation $translationService
     * @param GlobalState $globalStateService
     * @param \Enlight_Event_EventManager $eventManager
     */
    function __construct(
        Gateway\Product $productGateway,
        Price $priceService,
        Translation $translationService,
        GlobalState $globalStateService,
        \Enlight_Event_EventManager $eventManager
    ) {
        $this->productGateway = $productGateway;
        $this->priceService = $priceService;
        $this->translationService = $translationService;
        $this->globalStateService = $globalStateService;
        $this->eventManager = $eventManager;
    }


    /**
     * Returns a minified product variant which contains only
     * simplify data of a variant.
     * The product data is fully calculated, which means
     * that the product data is already translated and
     * the product prices are calculated to the current global state
     * of the shop.
     *
     * This product type is normally used for product overviews
     * like listings or sliders.
     *
     * To get the whole product data you can use the `get` function.
     *
     * @param string $number
     * @return Struct\ProductMini
     */
    public function getMini($number)
    {
        $product = $this->productGateway->getMini($number);

        $state = $this->globalStateService->get();

        $product->setPrices(
            $this->priceService->getProductPrices($product, $state)
        );

        $product->setCheapestPrice(
            $this->priceService->getCheapestProductPrice($product, $state)
        );


        $this->priceService->calculateProduct($product, $state);

        $this->translationService->translateProduct($product, $state);

        return $product;
    }
}
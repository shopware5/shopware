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
     * @var Media
     */
    private $mediaService;

    /**
     * @param Gateway\Product $productGateway
     * @param Price $priceService
     * @param Media $mediaService
     * @param Translation $translationService
     * @param \Enlight_Event_EventManager $eventManager
     */
    function __construct(
        Gateway\Product $productGateway,
        Price $priceService,
        Media $mediaService,
        Translation $translationService,
        \Enlight_Event_EventManager $eventManager
    ) {
        $this->productGateway = $productGateway;
        $this->priceService = $priceService;
        $this->mediaService = $mediaService;
        $this->translationService = $translationService;
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
     * @param \Shopware\Struct\GlobalState $state
     * @return Struct\ProductMini
     */
    public function getMini($number, Struct\GlobalState $state)
    {
        $product = $this->productGateway->getMini($number);

        $product->setPrices(
            $this->priceService->getProductPrices($product, $state)
        );

        $product->setCheapestVariantPrice(
            $this->priceService->getCheapestVariantPrice($product)
        );

        $product->setCheapestProductPrice(
            $this->priceService->getCheapestProductPrice($product, $state)
        );

        $product->setCover(
            $this->mediaService->getProductCover($product)
        );

        $this->priceService->calculateProduct($product, $state);

        $this->translationService->translateProduct($product, $state->getShop());

        return $product;
    }
}
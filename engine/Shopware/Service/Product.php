<?php

namespace Shopware\Service;

use Shopware\Struct as Struct;
use Shopware\Gateway as Gateway;

class Product
{
    /**
     * @var Gateway\ListProduct
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
     * @var \Shopware\Gateway\Vote
     */
    private $voteGateway;

    /**
     * @param Gateway\ListProduct $productGateway
     * @param Price $priceService
     * @param Media $mediaService
     * @param Translation $translationService
     * @param \Enlight_Event_EventManager $eventManager
     * @param \Shopware\Gateway\Vote $voteGateway
     */
    function __construct(
        Gateway\ListProduct $productGateway,
        Price $priceService,
        Media $mediaService,
        Translation $translationService,
        \Enlight_Event_EventManager $eventManager,
        Gateway\Vote $voteGateway
    ) {
        $this->productGateway = $productGateway;
        $this->priceService = $priceService;
        $this->mediaService = $mediaService;
        $this->translationService = $translationService;
        $this->eventManager = $eventManager;
        $this->voteGateway = $voteGateway;
    }


    /**
     * Returns a minified product variant which contains only
     * simplify data of a variant.
     *
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
     * @param \Shopware\Struct\Context $context
     * @return Struct\ListProduct
     */
    public function getListProduct($number, Struct\Context $context)
    {
        $product = $this->productGateway->getListProduct($number, $context);

        if (!$product || !$product->getId()) {
            return null;
        }

        $product->setPriceRules(
            $this->priceService->getProductPrices($product, $context)
        );

        $product->setCheapestPriceRule(
            $this->priceService->getCheapestPrice($product, $context)
        );

        $product->setCover(
            $this->mediaService->getProductCover($product)
        );

        $this->priceService->calculateProduct($product, $context);

        if (!$product->hasState(Struct\ListProduct::STATE_TRANSLATED)) {
            $this->translationService->translateProduct($product, $context->getShop());
        }

        return $product;
    }
}
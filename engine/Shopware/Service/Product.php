<?php

namespace Shopware\Service;

use Shopware\Struct as Struct;
use Shopware\Gateway as Gateway;

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
     * @var \Shopware\Gateway\Vote
     */
    private $voteGateway;

    /**
     * @param Gateway\Product $productGateway
     * @param Price $priceService
     * @param Media $mediaService
     * @param Translation $translationService
     * @param \Enlight_Event_EventManager $eventManager
     * @param \Shopware\Gateway\Vote $voteGateway
     */
    function __construct(
        Gateway\Product $productGateway,
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
     * @return Struct\ProductMini
     */
    public function getMini($number, Struct\Context $context)
    {
        $product = $this->productGateway->getMini($number);

        if (!$product || !$product->getId()) {
            return null;
        }

        $product->setPrices(
            $this->priceService->getProductPrices($product, $context)
        );

        $product->setCheapestPrice(
            $this->priceService->getCheapestPrice($product, $context)
        );

        $product->setCover(
            $this->mediaService->getProductCover($product)
        );

        $this->priceService->calculateProduct($product, $context);

        if (!$product->hasState(Struct\ProductMini::STATE_TRANSLATED)) {
            $this->translationService->translateProduct($product, $context->getShop());
        }

        return $product;
    }

    /**
     * Return a struct object which contains the product vote meta information.
     *
     * @param Struct\ProductMini $product
     * @return \Shopware\Struct\VoteAverage
     */
    public function getVoteAverage(Struct\ProductMini $product)
    {
        return $this->voteGateway->getAverage($product);
    }
}
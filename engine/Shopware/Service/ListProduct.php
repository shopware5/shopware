<?php

namespace Shopware\Service;

use Shopware\Struct;
use Shopware\Gateway;

class ListProduct
{
    /**
     * @var Gateway\DBAL\ListProduct
     */
    private $productGateway;

    /**
     * @var Price
     */
    private $priceService;

    /**
     * @var CheapestPrice
     */
    private $cheapestPriceService;

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
     * @var Gateway\DBAL\Vote
     */
    private $voteGateway;

    /**
     * @param Gateway\DBAL\ListProduct $productGateway
     * @param Price $priceService
     * @param CheapestPrice $cheapestPriceService
     * @param Media $mediaService
     * @param Translation $translationService
     * @param \Enlight_Event_EventManager $eventManager
     * @param \Shopware\Gateway\DBAL\Vote $voteGateway
     */
    function __construct(
        Gateway\DBAL\ListProduct $productGateway,
        Price $priceService,
        CheapestPrice $cheapestPriceService,
        Media $mediaService,
        Translation $translationService,
        \Enlight_Event_EventManager $eventManager,
        Gateway\DBAL\Vote $voteGateway
    ) {
        $this->productGateway = $productGateway;
        $this->priceService = $priceService;
        $this->mediaService = $mediaService;
        $this->translationService = $translationService;
        $this->eventManager = $eventManager;
        $this->voteGateway = $voteGateway;
        $this->cheapestPriceService = $cheapestPriceService;
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
     * @param array $numbers
     * @param \Shopware\Struct\Context $context
     * @return Struct\ListProduct[]
     */
    public function getList(array $numbers, Struct\Context $context)
    {
        $products = $this->productGateway->getList($numbers, $context);

        $covers = $this->mediaService->getCovers($products, $context);

        $prices = $this->priceService->getProductPriceList($products, $context);

        $cheapestPrices = $this->cheapestPriceService->getList($products, $context);

        foreach($products as $product) {
            $key = $product->getVariantId();

            $product->setCover($covers[$key]);

            $product->setPriceRules($prices[$key]);

            $product->setCheapestPriceRule($cheapestPrices[$key]);

            $this->priceService->calculateProduct($product, $context);

            $this->translationService->translateProduct($product, $context->getShop());
        }

        return $products;
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
    public function get($number, Struct\Context $context)
    {
        $products = $this->getList(array($number), $context);

        return array_shift($products);
    }
}
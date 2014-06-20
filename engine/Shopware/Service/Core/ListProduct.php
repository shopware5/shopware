<?php

namespace Shopware\Service\Core;

use Shopware\Struct;
use Shopware\Service;
use Shopware\Gateway;

class ListProduct implements Service\ListProduct
{
    /**
     * @var Gateway\ListProduct
     */
    private $productGateway;

    /**
     * @var Service\Media
     */
    private $mediaService;

    /**
     * @var Service\CheapestPrice
     */
    private $cheapestPriceService;

    /**
     * @var Service\GraduatedPrices
     */
    private $graduatedPricesService;

    /**
     * @var Service\PriceCalculation
     */
    private $priceCalculationService;

    /**
     * @var Service\Marketing
     */
    private $marketingService;

    /**
     * @var \Enlight_Event_EventManager
     */
    private $eventManager;

    /**
     * @param Gateway\ListProduct $productGateway
     * @param Service\GraduatedPrices $graduatedPricesService
     * @param Service\CheapestPrice $cheapestPriceService
     * @param Service\PriceCalculation $priceCalculationService
     * @param Service\Media $mediaService
     * @param Service\Marketing $marketingService
     * @param \Enlight_Event_EventManager $eventManager
     */
    function __construct(
        Gateway\ListProduct $productGateway,
        Service\GraduatedPrices $graduatedPricesService,
        Service\CheapestPrice $cheapestPriceService,
        Service\PriceCalculation $priceCalculationService,
        Service\Media $mediaService,
        Service\Marketing $marketingService,
        \Enlight_Event_EventManager $eventManager
    ) {
        $this->productGateway = $productGateway;
        $this->graduatedPricesService = $graduatedPricesService;
        $this->cheapestPriceService = $cheapestPriceService;
        $this->priceCalculationService = $priceCalculationService;
        $this->mediaService = $mediaService;
        $this->eventManager = $eventManager;
        $this->marketingService = $marketingService;
    }

    /**
     * Returns a full \Shopware\Struct\ListProduct object.
     *
     * A full \Shopware\Struct\ListProduct is build over the following classes:
     * - \Shopware\Gateway\ListProduct      > Selects the base product data
     * - \Shopware\Service\Media            > Selects the cover
     * - \Shopware\Service\GraduatedPrices  > Selects the graduated prices
     * - \Shopware\Service\CheapestPrice    > Selects the cheapest price
     *
     * This data will be injected into the generated \Shopware\Struct\ListProduct object
     * and will be calculated through the \Shopware\Service\PriceCalculation class.
     *
     * @param string $number
     * @param Struct\Context $context
     * @return Struct\ListProduct
     */
    public function get($number, Struct\Context $context)
    {
        $products = $this->getList(array($number), $context);

        return array_shift($products);
    }

    /**
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the @see \Shopware\Service\ListProduct::get()
     *
     * @param array $numbers
     * @param Struct\Context $context
     * @return Struct\ListProduct[] Indexed by the product order number.
     */
    public function getList(array $numbers, Struct\Context $context)
    {
        $products = $this->productGateway->getList($numbers, $context);

        $covers = $this->mediaService->getCovers($products, $context);

        $graduatedPrices = $this->graduatedPricesService->getList($products, $context);

        $cheapestPrices = $this->cheapestPriceService->getList($products, $context);

        $result = array();
        foreach ($numbers as $number) {
            if (!array_key_exists($number, $products)) {
                continue;
            }
            $product = $products[$number];

            $product->setCover($covers[$number]);

            $product->setPriceRules($graduatedPrices[$number]);

            $product->setCheapestPriceRule($cheapestPrices[$number]);

            $product->addAttribute(
                'marketing',
                $this->marketingService->getProductAttribute($product)
            );

            $this->priceCalculationService->calculateProduct($product, $context);

            $result[$number] = $product;
        }

        return $result;
    }
}

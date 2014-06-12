<?php

namespace Shopware\Service;

use Shopware\Struct;
use Shopware\Gateway\DBAL as Gateway;

class Product
{
    /**
     * @var Gateway\Product
     */
    private $productGateway;

    /**
     * @var Media
     */
    private $mediaService;

    /**
     * @var GraduatedPrices
     */
    private $graduatedPricesService;

    /**
     * @var PriceCalculation
     */
    private $priceCalculationService;

    /**
     * @var Vote
     */
    private $voteService;

    /**
     * @var \Enlight_Event_EventManager
     */
    private $eventManager;

    /**
     * @var RelatedProducts
     */
    private $relatedProductsService;

    /**
     * @var SimilarProducts
     */
    private $similarProductsService;

    /**
     * @var ProductDownload
     */
    private $downloadService;

    /**
     * @var ProductLink
     */
    private $linkService;

    /**
     * @var Property
     */
    private $propertyService;

    /**
     * @var Configurator
     */
    private $configuratorService;

    /**
     * @var CheapestPrice
     */
    private $cheapestPriceService;

    function __construct(
        Gateway\ListProduct $productGateway,
        Vote $voteService,
        RelatedProducts $relatedProductsService,
        SimilarProducts $similarProductsService,
        ListProduct $listProductService,
        GraduatedPrices $graduatedPricesService,
        CheapestPrice $cheapestPriceService,
        PriceCalculation $priceCalculationService,
        Media $mediaService,
        ProductDownload $downloadService,
        ProductLink $linkService,
        Property $propertyService,
        Configurator $configuratorService,
        \Enlight_Event_EventManager $eventManager
    ) {
        $this->productGateway = $productGateway;
        $this->voteService = $voteService;
        $this->relatedProductsService = $relatedProductsService;
        $this->similarProductsService = $similarProductsService;
        $this->downloadService = $downloadService;
        $this->linkService = $linkService;

        $this->listProductService = $listProductService;
        $this->graduatedPricesService = $graduatedPricesService;
        $this->cheapestPriceService = $cheapestPriceService;
        $this->priceCalculationService = $priceCalculationService;
        $this->mediaService = $mediaService;
        $this->propertyService = $propertyService;
        $this->configuratorService = $configuratorService;

        $this->eventManager = $eventManager;
    }

    /**
     * @param $numbers
     * @param Struct\Context $context
     * @return Struct\Product[]
     */
    public function getList($numbers, Struct\Context $context)
    {
        $products = $this->productGateway->getList($numbers, $context);

        $graduatedPrices = $this->graduatedPricesService->getList($products, $context);

        $cheapestPrice = $this->cheapestPriceService->getList($products, $context);

        $votes = $this->voteService->getList($products, $context);

        $relatedProducts = $this->relatedProductsService->getList($products, $context);

        $similarProducts = $this->similarProductsService->getList($product, $context);

        $downloads = $this->downloadService->getList($products, $context);

        $links = $this->linkService->getList($products);

        $media = $this->mediaService->getMedia($products, $context);

        $covers = $this->mediaService->getCovers($products, $context);

        $properties = $this->propertyService->getList($products, $context);

        $configuration = $this->configuratorService->getProductsConfigurations($products, $context);

        $result = array();
        foreach ($numbers as $number) {
            if (!array_key_exists($number, $products)) {
                continue;
            }

            $product = $products[$number];

            $product->hasState(Struct\ListProduct::STATE_PRICE_CALCULATED);

            $product->setRelatedProducts($relatedProducts[$number]);

            $product->setSimilarProducts($similarProducts[$number]);

            $product->setPriceRules($graduatedPrices[$number]);

            $product->setVotes($votes[$number]);

            $product->setDownloads($downloads[$number]);

            $product->setLinks($links[$number]);

            $product->setMedia($media[$number]);

            $product->setPropertySet($properties[$number]);

            $product->setConfiguration($configuration[$number]);

            $product->setCheapestPriceRule($cheapestPrice[$number]);

            $product->setCover($covers[$number]);

            $this->priceCalculationService->calculateProduct($product, $context);

            $result[$number] = $product;
        }

        return $result;
    }

}

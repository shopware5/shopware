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
     * @var Gateway\Vote
     */
    private $voteGateway;

    /**
     * @var \Enlight_Event_EventManager
     */
    private $eventManager;

    /**
     * @var Gateway\RelatedProducts
     */
    private $relatedProductsGateway;

    /**
     * @var Gateway\SimilarProducts
     */
    private $similarProductsGateway;

    /**
     * @var Gateway\Download
     */
    private $downloadGateway;

    /**
     * @var Gateway\Link
     */
    private $linkGateway;

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
        Gateway\Vote $voteGateway,
        Gateway\RelatedProducts $relatedProductsGateway,
        Gateway\SimilarProducts $similarProductsGateway,
        ListProduct $listProductService,
        GraduatedPrices $graduatedPricesService,
        CheapestPrice $cheapestPriceService,
        PriceCalculation $priceCalculationService,
        Media $mediaService,
        Gateway\Download $downloadGateway,
        Gateway\Link $linkGateway,
        Property $propertyService,
        Configurator $configuratorService,
        \Enlight_Event_EventManager $eventManager
    ) {
        $this->productGateway = $productGateway;
        $this->voteGateway = $voteGateway;
        $this->relatedProductsGateway = $relatedProductsGateway;
        $this->similarProductsGateway = $similarProductsGateway;
        $this->downloadGateway = $downloadGateway;
        $this->linkGateway = $linkGateway;

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

        $votes = $this->voteGateway->getList($products);

        $relatedProducts = $this->getRelatedProducts($products, $context);

        $similarProducts = $this->getSimilarProducts($products, $context);

        $downloads = $this->downloadGateway->getList($products);

        $links = $this->linkGateway->getList($products);

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

    /**
     * @param Struct\ListProduct[] $products
     * @param Struct\Context $context
     * @return array Indexed with the product number, the values are a list of ListProduct structs.
     */
    public function getRelatedProducts(array $products, Struct\Context $context)
    {
        /**
         * returns an array which is associated with the different product numbers.
         * Each array contains a list of product numbers which are related to the reference product.
         */
        $numbers = $this->relatedProductsGateway->getList($products);

        //loads the list product data for the selected numbers.
        //all numbers are joined in the extractNumbers function to prevent that a product will be
        //loaded multiple times
        $listProducts = $this->listProductService->getList(
            $this->extractNumbers($numbers),
            $context
        );

        $result = array();
        foreach ($products as $product) {
            $result[$product->getNumber()] = $this->getProductsByNumbers(
                $listProducts,
                $numbers[$product->getId()]
            );
        }

        return $result;
    }

    /**
     * @param Struct\ListProduct[] $products
     * @param Struct\Context $context
     * @return array Indexed with the product number, the values are a list of ListProduct structs.
     */
    public function getSimilarProducts(array $products, Struct\Context $context)
    {
        /**
         * returns an array which is associated with the different product numbers.
         * Each array contains a list of product numbers which are related to the reference product.
         */
        $numbers = $this->similarProductsGateway->getList($products);

        //loads the list product data for the selected numbers.
        //all numbers are joined in the extractNumbers function to prevent that a product will be
        //loaded multiple times
        $listProducts = $this->listProductService->getList(
            $this->extractNumbers($numbers),
            $context
        );

        $result = array();
        foreach ($products as $product) {
            $result[$product->getNumber()] = $this->getProductsByNumbers(
                $listProducts,
                $numbers[$product->getId()]
            );
        }

        //todo@dr fallback to same category products.
//        $similar = $this->similarProductsGateway->getList($fallback);
//
//        foreach($fallback as $product) {
//            $number = $product->getNumber();
//
//            if (!array_key_exists($number, $similar)) {
//                continue;
//            }
//
//            $result[$number] = $similar[$number];
//        }

        return $result;
    }


    /**
     * @param Struct\ListProduct[] $products
     * @param array $numbers
     * @return Struct\ListProduct[]
     */
    private function getProductsByNumbers(array $products, array $numbers)
    {
        $result = array();

        foreach ($products as $product) {
            if (in_array($product->getNumber(), $numbers)) {
                $result[] = $product;
            }
        }
        return $result;
    }


    /**
     * @param $numbers
     * @return array
     */
    private function extractNumbers($numbers)
    {
        //collect all numbers to send a single list product request.
        $related = array();
        foreach ($numbers as $value) {
            $related = array_merge($related, $value);
        }

        //filter duplicate numbers to prevent duplicate data requests and iterations.
        $unique = array_unique($related);
        return array_values($unique);
    }
}
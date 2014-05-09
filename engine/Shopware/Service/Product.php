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
     * @var Translation
     */
    private $translationService;

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


    function __construct(
        Gateway\ListProduct $productGateway,
        Gateway\Vote $voteGateway,
        Gateway\RelatedProducts $relatedProductsGateway,
        Gateway\SimilarProducts $similarProductsGateway,
        ListProduct $listProductService,
        GraduatedPrices $graduatedPricesService,
        PriceCalculation $priceCalculationService,
        Media $mediaService,
        Translation $translationService,
        \Enlight_Event_EventManager $eventManager
    ) {
        $this->productGateway = $productGateway;

        $this->voteGateway = $voteGateway;

        $this->relatedProductsGateway = $relatedProductsGateway;

        $this->similarProductsGateway = $similarProductsGateway;

        $this->listProductService = $listProductService;

        $this->graduatedPricesService = $graduatedPricesService;

        $this->priceCalculationService = $priceCalculationService;

        $this->mediaService = $mediaService;

        $this->translationService = $translationService;

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

        $votes = $this->voteGateway->getList($products);

        $related = $this->getRelatedProducts($products, $context);

        $similar = $this->getSimilarProducts($products, $context);

        $result = array();
        foreach($numbers as $number) {
            if (!array_key_exists($number, $products)) {
                continue;
            }

            $product = $products[$number];

            $product->hasState(Struct\ListProduct::STATE_PRICE_CALCULATED);


            $product->setRelated($related[$number]);

            $product->setVotes($votes[$number]);

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
        $related = $this->listProductService->getList(
            $this->extractNumbers($numbers),
            $context
        );

        $result = array();
        foreach($numbers as $key => $relatedProducts) {

            //collects all elements of $related, which order number are stored in the $numbers array
            $result[$key] = array_filter($related, function (Struct\ListProduct $product) use ($relatedProducts) {
                return (in_array($product->getNumber(), $relatedProducts));
            });
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
        $numbers = $this->similarProductsGateway->getList($products);

        //loads the list product data for the selected numbers.
        //all numbers are joined in the extractNumbers function to prevent that a product will be
        //loaded multiple times
        $similar = $this->listProductService->getList(
            $this->extractNumbers($numbers),
            $context
        );

        $fallback = array();
        $result = array();

        foreach($products as $product) {
            $number = $product->getNumber();

            if (!array_key_exists($number, $numbers)) {
                $fallback[] = $product;
                continue;
            }

            $similarNumbers = $numbers[$number];

            $result[$number] = array_filter($similar, function(Struct\ListProduct $similarProduct) use ($similarNumbers) {
                return in_array($similarProduct->getNumber(), $similarNumbers);
            });
        }

        if (empty($fallback)) {
            return $result;
        }

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
     * @param $numbers
     * @return array
     */
    private function extractNumbers($numbers)
    {
        //collect all numbers to send a single list product request.
        $related = array();
        foreach($numbers as $value) {
            $related = array_merge($related, $value);
        }

        //filter duplicate numbers to prevent duplicate data requests and iterations.
        $unique = array_unique($related);
        return array_values($unique);
    }
}
<?php
/**
 * Shopware 4
 * Copyright © shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */
namespace Shopware\Service\Core;

use Shopware\Struct;
use Shopware\Service;
use Shopware\Gateway;

/**
 * @package Shopware\Service\Core
 */
class Product implements Service\Product
{
    /**
     * @var Gateway\Product
     */
    private $productGateway;

    /**
     * @var Service\Media
     */
    private $mediaService;

    /**
     * @var Service\GraduatedPrices
     */
    private $graduatedPricesService;

    /**
     * @var Service\PriceCalculation
     */
    private $priceCalculationService;

    /**
     * @var Service\Vote
     */
    private $voteService;

    /**
     * @var \Enlight_Event_EventManager
     */
    private $eventManager;

    /**
     * @var Service\RelatedProducts
     */
    private $relatedProductsService;

    /**
     * @var Service\SimilarProducts
     */
    private $similarProductsService;

    /**
     * @var Service\ProductDownload
     */
    private $downloadService;

    /**
     * @var Service\ProductLink
     */
    private $linkService;

    /**
     * @var Service\Property
     */
    private $propertyService;

    /**
     * @var Service\Configurator
     */
    private $configuratorService;

    /**
     * @var Service\CheapestPrice
     */
    private $cheapestPriceService;

    /**
     * @var Service\Marketing
     */
    private $marketingService;

    /**
     * @param Gateway\ListProduct $productGateway
     * @param Service\Vote $voteService
     * @param Service\RelatedProducts $relatedProductsService
     * @param Service\SimilarProducts $similarProductsService
     * @param Service\ListProduct $listProductService
     * @param Service\GraduatedPrices $graduatedPricesService
     * @param Service\CheapestPrice $cheapestPriceService
     * @param Service\PriceCalculation $priceCalculationService
     * @param Service\Media $mediaService
     * @param Service\ProductDownload $downloadService
     * @param Service\ProductLink $linkService
     * @param Service\Property $propertyService
     * @param Service\Configurator $configuratorService
     * @param Service\Marketing $marketingService
     * @param \Enlight_Event_EventManager $eventManager
     */
    function __construct(
        Gateway\ListProduct $productGateway,
        Service\Vote $voteService,
        Service\RelatedProducts $relatedProductsService,
        Service\SimilarProducts $similarProductsService,
        Service\ListProduct $listProductService,
        Service\GraduatedPrices $graduatedPricesService,
        Service\CheapestPrice $cheapestPriceService,
        Service\PriceCalculation $priceCalculationService,
        Service\Media $mediaService,
        Service\ProductDownload $downloadService,
        Service\ProductLink $linkService,
        Service\Property $propertyService,
        Service\Configurator $configuratorService,
        Service\Marketing $marketingService,
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
        $this->marketingService = $marketingService;
        $this->eventManager = $eventManager;
    }

    /**
     * @inheritdoc
     */
    public function get($number, Struct\Context $context)
    {
        $products = $this->getList(array($number), $context);
        return array_shift($products);
    }

    /**
     * @inheritdoc
     */
    public function getList($numbers, Struct\Context $context)
    {
        $products = $this->productGateway->getList($numbers, $context);

        $graduatedPrices = $this->graduatedPricesService->getList($products, $context);

        $cheapestPrice = $this->cheapestPriceService->getList($products, $context);

        $votes = $this->voteService->getList($products, $context);

        $relatedProducts = $this->relatedProductsService->getList($products, $context);

        $similarProducts = $this->similarProductsService->getList($products, $context);

        $downloads = $this->downloadService->getList($products, $context);

        $links = $this->linkService->getList($products);

        $media = $this->mediaService->getProductsMedia($products, $context);

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

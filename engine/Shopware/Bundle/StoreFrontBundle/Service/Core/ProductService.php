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
namespace Shopware\Bundle\StoreFrontBundle\Service\Core;

use Shopware\Bundle\StoreFrontBundle\Struct;
use Shopware\Bundle\StoreFrontBundle\Service;
use Shopware\Bundle\StoreFrontBundle\Gateway;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\StoreFrontBundle\Service\Core
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ProductService implements Service\ProductServiceInterface
{
    /**
     * @var Gateway\ProductGatewayInterface
     */
    private $productGateway;

    /**
     * @var Service\MediaServiceInterface
     */
    private $mediaService;

    /**
     * @var Service\GraduatedPricesServiceInterface
     */
    private $graduatedPricesService;

    /**
     * @var Service\PriceCalculationServiceInterface
     */
    private $priceCalculationService;

    /**
     * @var Service\VoteServiceInterface
     */
    private $voteService;

    /**
     * @var \Enlight_Event_EventManager
     */
    private $eventManager;

    /**
     * @var Service\RelatedProductsServiceInterface
     */
    private $relatedProductsService;

    /**
     * @var Service\SimilarProductsServiceInterface
     */
    private $similarProductsService;

    /**
     * @var Service\ProductDownloadServiceInterface
     */
    private $downloadService;

    /**
     * @var Service\ProductLinkServiceInterface
     */
    private $linkService;

    /**
     * @var Service\PropertyServiceInterface
     */
    private $propertyService;

    /**
     * @var Service\ConfiguratorServiceInterface
     */
    private $configuratorService;

    /**
     * @var Service\CheapestPriceServiceInterface
     */
    private $cheapestPriceService;

    /**
     * @var Service\MarketingServiceInterface
     */
    private $marketingService;

    /**
     * @param Gateway\ListProductGatewayInterface $productGateway
     * @param Service\VoteServiceInterface $voteService
     * @param Service\RelatedProductsServiceInterface $relatedProductsService
     * @param Service\SimilarProductsServiceInterface $similarProductsService
     * @param Service\ListProductServiceInterface $listProductService
     * @param Service\GraduatedPricesServiceInterface $graduatedPricesService
     * @param Service\CheapestPriceServiceInterface $cheapestPriceService
     * @param Service\PriceCalculationServiceInterface $priceCalculationService
     * @param Service\MediaServiceInterface $mediaService
     * @param Service\ProductDownloadServiceInterface $downloadService
     * @param Service\ProductLinkServiceInterface $linkService
     * @param Service\PropertyServiceInterface $propertyService
     * @param Service\ConfiguratorServiceInterface $configuratorService
     * @param Service\MarketingServiceInterface $marketingService
     * @param \Enlight_Event_EventManager $eventManager
     */
    public function __construct(
        Gateway\ListProductGatewayInterface $productGateway,
        Service\VoteServiceInterface $voteService,
        Service\RelatedProductsServiceInterface $relatedProductsService,
        Service\SimilarProductsServiceInterface $similarProductsService,
        Service\ListProductServiceInterface $listProductService,
        Service\GraduatedPricesServiceInterface $graduatedPricesService,
        Service\CheapestPriceServiceInterface $cheapestPriceService,
        Service\PriceCalculationServiceInterface $priceCalculationService,
        Service\MediaServiceInterface $mediaService,
        Service\ProductDownloadServiceInterface $downloadService,
        Service\ProductLinkServiceInterface $linkService,
        Service\PropertyServiceInterface $propertyService,
        Service\ConfiguratorServiceInterface $configuratorService,
        Service\MarketingServiceInterface $marketingService,
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
    public function get($number, Struct\ProductContextInterface $context)
    {
        $products = $this->getList(array($number), $context);

        return array_shift($products);
    }

    /**
     * @inheritdoc
     */
    public function getList($numbers, Struct\ProductContextInterface $context)
    {
        $products = $this->productGateway->getList($numbers, $context);

        $graduatedPrices = $this->graduatedPricesService->getList($products, $context);

        $cheapestPrice = $this->cheapestPriceService->getList($products, $context);

        $votes = $this->voteService->getList($products, $context);

        $averages = $this->voteService->getAverages($products, $context);

        $relatedProducts = $this->relatedProductsService->getList($products, $context);

        $similarProducts = $this->similarProductsService->getList($products, $context);

        $downloads = $this->downloadService->getList($products, $context);

        $links = $this->linkService->getList($products, $context);

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

            if (isset($relatedProducts[$number])) {
                $product->setRelatedProducts($relatedProducts[$number]);
            }

            if (isset($similarProducts[$number])) {
                $product->setSimilarProducts($similarProducts[$number]);
            }

            if (isset($graduatedPrices[$number])) {
                $product->setPriceRules($graduatedPrices[$number]);
            }

            if (isset($votes[$number])) {
                $product->setVotes($votes[$number]);
            }

            if (isset($downloads[$number])) {
                $product->setDownloads($downloads[$number]);
            }

            if (isset($links[$number])) {
                $product->setLinks($links[$number]);
            }

            if (isset($media[$number])) {
                $product->setMedia($media[$number]);
            }

            if (isset($properties[$number])) {
                $product->setPropertySet($properties[$number]);
            }

            if (isset($configuration[$number])) {
                $product->setConfiguration($configuration[$number]);
            }

            if (isset($cheapestPrice[$number])) {
                $product->setCheapestPriceRule($cheapestPrice[$number]);
            }

            if (isset($covers[$number])) {
                $product->setCover($covers[$number]);
            }

            if (isset($averages[$number])) {
                $product->setVoteAverage($averages[$number]);
            }

            $product->addAttribute(
                'marketing',
                $this->marketingService->getProductAttribute($product)
            );

            $this->priceCalculationService->calculateProduct($product, $context);

            if ($this->isProductValid($product, $context)) {
                $result[$number] = $product;
            }
        }

        return $result;
    }

    /**
     * Checks if the provided product is allowed to display in the store front for
     * the provided context.
     *
     * @param Struct\Product $product
     * @param Struct\ProductContextInterface $context
     * @return bool
     */
    private function isProductValid(Struct\Product $product, Struct\ProductContextInterface $context)
    {
        if (in_array($context->getCurrentCustomerGroup()->getId(), $product->getBlockedCustomerGroupIds())) {
            return false;
        }

        $prices = $product->getPrices();
        if (empty($prices)) {
            return false;
        }

        return true;
    }
}

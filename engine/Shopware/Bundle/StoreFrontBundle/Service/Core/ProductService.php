<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
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
     * @var Service\MediaServiceInterface
     */
    private $mediaService;

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
     * @param Service\ListProductServiceInterface $listProductService
     * @param Service\VoteServiceInterface $voteService
     * @param Service\MediaServiceInterface $mediaService
     * @param Service\RelatedProductsServiceInterface $relatedProductsService
     * @param Service\SimilarProductsServiceInterface $similarProductsService
     * @param Service\ProductDownloadServiceInterface $downloadService
     * @param Service\ProductLinkServiceInterface $linkService
     * @param Service\PropertyServiceInterface $propertyService
     * @param Service\ConfiguratorServiceInterface $configuratorService
     * @param \Enlight_Event_EventManager $eventManager
     */
    public function __construct(
        Service\ListProductServiceInterface $listProductService,
        Service\VoteServiceInterface $voteService,
        Service\MediaServiceInterface $mediaService,
        Service\RelatedProductsServiceInterface $relatedProductsService,
        Service\SimilarProductsServiceInterface $similarProductsService,
        Service\ProductDownloadServiceInterface $downloadService,
        Service\ProductLinkServiceInterface $linkService,
        Service\PropertyServiceInterface $propertyService,
        Service\ConfiguratorServiceInterface $configuratorService,
        \Enlight_Event_EventManager $eventManager
    ) {
        $this->voteService = $voteService;
        $this->relatedProductsService = $relatedProductsService;
        $this->similarProductsService = $similarProductsService;
        $this->downloadService = $downloadService;
        $this->linkService = $linkService;
        $this->listProductService = $listProductService;
        $this->mediaService = $mediaService;
        $this->propertyService = $propertyService;
        $this->configuratorService = $configuratorService;
        $this->eventManager = $eventManager;
    }

    /**
     * @inheritdoc
     */
    public function get($number, Struct\ProductContextInterface $context)
    {
        $products = $this->getList([$number], $context);

        return array_shift($products);
    }

    /**
     * @inheritdoc
     */
    public function getList(array $numbers, Struct\ProductContextInterface $context)
    {
        $listProducts = $this->listProductService->getList($numbers, $context);

        return $this->createFromListProducts($listProducts, $context);
    }

    /**
     * @param Struct\ListProduct[] $listProducts
     * @param Struct\ProductContextInterface $context
     * @return Struct\Product[] indexed by order number
     */
    private function createFromListProducts(array $listProducts, Struct\ProductContextInterface $context)
    {
        $votes = $this->voteService->getList($listProducts, $context);

        $relatedProducts = $this->relatedProductsService->getList($listProducts, $context);

        $similarProducts = $this->similarProductsService->getList($listProducts, $context);

        $downloads = $this->downloadService->getList($listProducts, $context);

        $links = $this->linkService->getList($listProducts, $context);

        $media = $this->mediaService->getProductsMedia($listProducts, $context);

        $properties = $this->propertyService->getList($listProducts, $context);

        $configuration = $this->configuratorService->getProductsConfigurations($listProducts, $context);

        $products = [];
        foreach ($listProducts as $listProduct) {
            $number = $listProduct->getNumber();

            $product = $this->createProductStruct($listProduct);

            if (isset($relatedProducts[$number])) {
                $product->setRelatedProducts($relatedProducts[$number]);
            }

            if (isset($similarProducts[$number])) {
                $product->setSimilarProducts($similarProducts[$number]);
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

            $products[$number] = $product;
        }

        return $products;
    }

    /**
     * @param  Struct\ListProduct $listProduct
     * @return Struct\Product
     */
    private function createProductStruct(Struct\ListProduct $listProduct)
    {
        $product = new Struct\Product(
            $listProduct->getId(),
            $listProduct->getVariantId(),
            $listProduct->getNumber()
        );

        $product->setShippingFree($listProduct->isShippingFree());
        $product->setAllowsNotification($listProduct->allowsNotification());
        $product->setHighlight($listProduct->highlight());
        $product->setUnit($listProduct->getUnit());
        $product->setTax($listProduct->getTax());
        $product->setPrices($listProduct->getPrices());
        $product->setManufacturer($listProduct->getManufacturer());
        $product->setCover($listProduct->getCover());
        $product->setCheapestPrice($listProduct->getCheapestPrice());
        $product->setName($listProduct->getName());
        $product->setAdditional($listProduct->getAdditional());
        $product->setCloseouts($listProduct->isCloseouts());
        $product->setEan($listProduct->getEan());
        $product->setHeight($listProduct->getHeight());
        $product->setKeywords($listProduct->getKeywords());
        $product->setLength($listProduct->getLength());
        $product->setLongDescription($listProduct->getLongDescription());
        $product->setMinStock($listProduct->getMinStock());
        $product->setReleaseDate($listProduct->getReleaseDate());
        $product->setShippingTime($listProduct->getShippingTime());
        $product->setShortDescription($listProduct->getShortDescription());
        $product->setStock($listProduct->getStock());
        $product->setWeight($listProduct->getWeight());
        $product->setWidth($listProduct->getWidth());
        $product->setPriceGroup($listProduct->getPriceGroup());
        $product->setCreatedAt($listProduct->getCreatedAt());
        $product->setPriceRules($listProduct->getPriceRules());
        $product->setCheapestPriceRule($listProduct->getCheapestPriceRule());
        $product->setManufacturerNumber($listProduct->getManufacturerNumber());
        $product->setMetaTitle($listProduct->getMetaTitle());
        $product->setTemplate($listProduct->getTemplate());
        $product->setHasConfigurator($listProduct->hasConfigurator());
        $product->setSales($listProduct->getSales());
        $product->setHasEsd($listProduct->hasEsd());
        $product->setEsd($listProduct->getEsd());
        $product->setIsPriceGroupActive($listProduct->isPriceGroupActive());
        $product->setBlockedCustomerGroupIds($listProduct->getBlockedCustomerGroupIds());
        $product->setVoteAverage($listProduct->getVoteAverage());

        foreach ($listProduct->getAttributes() as $name => $attribute) {
            $product->addAttribute($name, $attribute);
        }

        foreach ($listProduct->getStates() as $state) {
            $product->addState($state);
        }

        return $product;
    }
}

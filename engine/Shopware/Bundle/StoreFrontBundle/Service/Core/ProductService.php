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

use Shopware\Bundle\StoreFrontBundle\Service;
use Shopware\Bundle\StoreFrontBundle\Struct;

/**
 * @category  Shopware
 *
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
     * @var Service\RelatedProductStreamsServiceInterface
     */
    private $relatedProductStreamsService;

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
     * @var Service\ListProductServiceInterface
     */
    private $listProductService;

    /**
     * @param Service\ListProductServiceInterface           $listProductService
     * @param Service\VoteServiceInterface                  $voteService
     * @param Service\MediaServiceInterface                 $mediaService
     * @param Service\RelatedProductsServiceInterface       $relatedProductsService
     * @param Service\RelatedProductStreamsServiceInterface $relatedProductStreamsService
     * @param Service\SimilarProductsServiceInterface       $similarProductsService
     * @param Service\ProductDownloadServiceInterface       $downloadService
     * @param Service\ProductLinkServiceInterface           $linkService
     * @param Service\PropertyServiceInterface              $propertyService
     * @param Service\ConfiguratorServiceInterface          $configuratorService
     * @param \Enlight_Event_EventManager                   $eventManager
     */
    public function __construct(
        Service\ListProductServiceInterface $listProductService,
        Service\VoteServiceInterface $voteService,
        Service\MediaServiceInterface $mediaService,
        Service\RelatedProductsServiceInterface $relatedProductsService,
        Service\RelatedProductStreamsServiceInterface $relatedProductStreamsService,
        Service\SimilarProductsServiceInterface $similarProductsService,
        Service\ProductDownloadServiceInterface $downloadService,
        Service\ProductLinkServiceInterface $linkService,
        Service\PropertyServiceInterface $propertyService,
        Service\ConfiguratorServiceInterface $configuratorService,
        \Enlight_Event_EventManager $eventManager
    ) {
        $this->voteService = $voteService;
        $this->relatedProductsService = $relatedProductsService;
        $this->relatedProductStreamsService = $relatedProductStreamsService;
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
     * {@inheritdoc}
     */
    public function getList(array $numbers, Struct\ProductContextInterface $context)
    {
        $listProducts = $this->listProductService->getList($numbers, $context);
        $products = $this->createFromListProducts($listProducts, $context);

        return $products;
    }

    /**
     * @param Struct\ListProduct[]           $listProducts
     * @param Struct\ProductContextInterface $context
     *
     * @return Struct\Product[] indexed by order number
     */
    private function createFromListProducts(array $listProducts, Struct\ProductContextInterface $context)
    {
        $votes = $this->voteService->getList($listProducts, $context);

        $relatedProducts = $this->relatedProductsService->getList($listProducts, $context);

        $relatedProductStreams = $this->relatedProductStreamsService->getList($listProducts, $context);

        $similarProducts = $this->similarProductsService->getList($listProducts, $context);

        $downloads = $this->downloadService->getList($listProducts, $context);

        $links = $this->linkService->getList($listProducts, $context);

        $media = $this->mediaService->getProductsMedia($listProducts, $context);

        $properties = $this->propertyService->getList($listProducts, $context);

        $configuration = $this->configuratorService->getProductsConfigurations($listProducts, $context);

        $products = [];
        foreach ($listProducts as $listProduct) {
            $number = $listProduct->getNumber();

            $product = Struct\Product::createFromListProduct($listProduct);

            if (isset($relatedProducts[$number])) {
                $product->setRelatedProducts($relatedProducts[$number]);
            }

            if (isset($relatedProductStreams[$number])) {
                $product->setRelatedProductStreams($relatedProductStreams[$number]);
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
}

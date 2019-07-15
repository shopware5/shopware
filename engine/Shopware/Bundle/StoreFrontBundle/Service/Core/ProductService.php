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

use Shopware\Bundle\StoreFrontBundle\Service\ConfiguratorServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ListProductServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\MediaServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ProductDownloadServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ProductLinkServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ProductServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\PropertyServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\RelatedProductsServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\RelatedProductStreamsServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\SimilarProductsServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\VoteServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\Product;
use Shopware\Bundle\StoreFrontBundle\Struct\ProductContextInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class ProductService implements ProductServiceInterface
{
    /**
     * @var MediaServiceInterface
     */
    private $mediaService;

    /**
     * @var VoteServiceInterface
     */
    private $voteService;

    /**
     * @var RelatedProductsServiceInterface
     */
    private $relatedProductsService;

    /**
     * @var RelatedProductStreamsServiceInterface
     */
    private $relatedProductStreamsService;

    /**
     * @var SimilarProductsServiceInterface
     */
    private $similarProductsService;

    /**
     * @var ProductDownloadServiceInterface
     */
    private $downloadService;

    /**
     * @var ProductLinkServiceInterface
     */
    private $linkService;

    /**
     * @var PropertyServiceInterface
     */
    private $propertyService;

    /**
     * @var ConfiguratorServiceInterface
     */
    private $configuratorService;

    /**
     * @var ListProductServiceInterface
     */
    private $listProductService;

    public function __construct(
        ListProductServiceInterface $listProductService,
        VoteServiceInterface $voteService,
        MediaServiceInterface $mediaService,
        RelatedProductsServiceInterface $relatedProductsService,
        RelatedProductStreamsServiceInterface $relatedProductStreamsService,
        SimilarProductsServiceInterface $similarProductsService,
        ProductDownloadServiceInterface $downloadService,
        ProductLinkServiceInterface $linkService,
        PropertyServiceInterface $propertyService,
        ConfiguratorServiceInterface $configuratorService
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
    }

    /**
     * {@inheritdoc}
     */
    public function get($number, ProductContextInterface $context)
    {
        $products = $this->getList([$number], $context);

        return array_shift($products);
    }

    /**
     * {@inheritdoc}
     */
    public function getList(array $numbers, ProductContextInterface $context)
    {
        $listProducts = $this->listProductService->getList($numbers, $context);

        return $this->createFromListProducts($listProducts, $context);
    }

    /**
     * @param ListProduct[] $listProducts
     *
     * @return Product[] indexed by order number
     */
    private function createFromListProducts(array $listProducts, ShopContextInterface $context)
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
        foreach ($listProducts as $number => $listProduct) {
            $product = Product::createFromListProduct($listProduct);

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

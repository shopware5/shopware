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

namespace Shopware\Bundle\StoreFrontBundle\Product;

use Shopware\Bundle\StoreFrontBundle\Configurator\ConfiguratorServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Context\ShopContextInterface;
use Shopware\Bundle\StoreFrontBundle\Media\MediaServiceInterface;
use Shopware\Bundle\StoreFrontBundle\ProductDownload\ProductDownloadServiceInterface;
use Shopware\Bundle\StoreFrontBundle\ProductLink\ProductLinkServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Property\PropertyServiceInterface;
use Shopware\Bundle\StoreFrontBundle\RelatedProduct\RelatedProductServiceInterface;
use Shopware\Bundle\StoreFrontBundle\RelatedProduct\RelatedProductStreamServiceInterface;
use Shopware\Bundle\StoreFrontBundle\SimilarProduct\SimilarProductServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Vote\VoteServiceInterface;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ProductService implements ProductServiceInterface
{
    /**
     * @var \Shopware\Bundle\StoreFrontBundle\Media\MediaServiceInterface
     */
    private $mediaService;

    /**
     * @var VoteServiceInterface
     */
    private $voteService;

    /**
     * @var \Shopware\Bundle\StoreFrontBundle\RelatedProduct\RelatedProductServiceInterface
     */
    private $relatedProductsService;

    /**
     * @var \Shopware\Bundle\StoreFrontBundle\RelatedProduct\RelatedProductStreamServiceInterface
     */
    private $relatedProductStreamsService;

    /**
     * @var \Shopware\Bundle\StoreFrontBundle\SimilarProduct\SimilarProductServiceInterface
     */
    private $similarProductsService;

    /**
     * @var \Shopware\Bundle\StoreFrontBundle\ProductDownload\ProductDownloadServiceInterface
     */
    private $downloadService;

    /**
     * @var ProductLinkServiceInterface
     */
    private $linkService;

    /**
     * @var \Shopware\Bundle\StoreFrontBundle\Property\PropertyServiceInterface
     */
    private $propertyService;

    /**
     * @var \Shopware\Bundle\StoreFrontBundle\Configurator\ConfiguratorServiceInterface
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
        RelatedProductServiceInterface $relatedProductsService,
        RelatedProductStreamServiceInterface $relatedProductStreamsService,
        SimilarProductServiceInterface $similarProductsService,
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
    public function getList(array $numbers, ShopContextInterface $context)
    {
        $listProducts = $this->listProductService->getList($numbers, $context);
        $products = $this->createFromListProducts($listProducts, $context);

        return $products;
    }

    /**
     * @param ListProduct[]                                                  $listProducts
     * @param \Shopware\Bundle\StoreFrontBundle\Context\ShopContextInterface $context
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
        foreach ($listProducts as $listProduct) {
            $number = $listProduct->getNumber();

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

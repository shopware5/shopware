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

use Shopware\Bundle\StoreFrontBundle\Gateway\ListProductGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Service\CategoryServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\CheapestPriceServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\GraduatedPricesServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ListProductServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\MarketingServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\MediaServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\PriceCalculationServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\VoteServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ProductContextInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware_Components_Config;

class ListProductService implements ListProductServiceInterface
{
    /**
     * @var ListProductGatewayInterface
     */
    private $productGateway;

    /**
     * @var MediaServiceInterface
     */
    private $mediaService;

    /**
     * @var CheapestPriceServiceInterface
     */
    private $cheapestPriceService;

    /**
     * @var GraduatedPricesServiceInterface
     */
    private $graduatedPricesService;

    /**
     * @var PriceCalculationServiceInterface
     */
    private $priceCalculationService;

    /**
     * @var MarketingServiceInterface
     */
    private $marketingService;

    /**
     * @var VoteServiceInterface
     */
    private $voteService;

    /**
     * @var CategoryServiceInterface
     */
    private $categoryService;

    /**
     * @var Shopware_Components_Config
     */
    private $config;

    public function __construct(
        ListProductGatewayInterface $productGateway,
        GraduatedPricesServiceInterface $graduatedPricesService,
        CheapestPriceServiceInterface $cheapestPriceService,
        PriceCalculationServiceInterface $priceCalculationService,
        MediaServiceInterface $mediaService,
        MarketingServiceInterface $marketingService,
        VoteServiceInterface $voteService,
        CategoryServiceInterface $categoryService,
        Shopware_Components_Config $config
    ) {
        $this->productGateway = $productGateway;
        $this->graduatedPricesService = $graduatedPricesService;
        $this->cheapestPriceService = $cheapestPriceService;
        $this->priceCalculationService = $priceCalculationService;
        $this->mediaService = $mediaService;
        $this->marketingService = $marketingService;
        $this->voteService = $voteService;
        $this->categoryService = $categoryService;
        $this->config = $config;
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
        // faster replacement for array_unique()
        // see http://stackoverflow.com/questions/8321620/array-unique-vs-array-flip
        $numbers = array_keys(array_flip($numbers));

        $products = $this->productGateway->getList($numbers, $context);

        $covers = $this->mediaService->getCovers($products, $context);

        $graduatedPrices = $this->graduatedPricesService->getList($products, $context);

        $cheapestPrices = $this->cheapestPriceService->getList($products, $context);

        $voteAverages = $this->voteService->getAverages($products, $context);

        $categories = $this->categoryService->getProductsCategories($products, $context);

        $manufacturerCovers = $this->mediaService->getList($this->getManufacturerCoverIds($products), $context);

        $result = [];
        foreach ($numbers as $number) {
            if (!array_key_exists($number, $products)) {
                continue;
            }
            $product = $products[$number];

            if (isset($covers[$number])) {
                $product->setCover($covers[$number]);
            }

            if (isset($graduatedPrices[$number])) {
                $product->setPriceRules($graduatedPrices[$number]);
            }

            if (isset($cheapestPrices[$number])) {
                $product->setCheapestPriceRule($cheapestPrices[$number]);
            }

            if (isset($voteAverages[$number])) {
                $product->setVoteAverage($voteAverages[$number]);
            }

            if (isset($categories[$number])) {
                $product->setCategories($categories[$number]);
            }

            if (isset($manufacturerCovers[$product->getManufacturer()->getCoverId()])) {
                $product->getManufacturer()->setCoverMedia($manufacturerCovers[$product->getManufacturer()->getCoverId()]);
            }

            $product->addAttribute('marketing', $this->marketingService->getProductAttribute($product));

            $this->priceCalculationService->calculateProduct($product, $context);

            if (!$this->isProductValid($product, $context)) {
                continue;
            }

            $product->setListingPrice($product->getCheapestUnitPrice());
            $product->setDisplayFromPrice((count($product->getPrices()) > 1 || $product->hasDifferentPrices()));
            $product->setAllowBuyInListing($this->allowBuyInListing($product));
            if ($this->config->get('calculateCheapestPriceWithMinPurchase')) {
                $product->setListingPrice($product->getCheapestPrice());
            }
            $result[$number] = $product;
        }

        return $result;
    }

    /**
     * Checks if the provided product is allowed to display in the store front for
     * the provided context.
     *
     * @return bool
     */
    private function isProductValid(ListProduct $product, ShopContextInterface $context)
    {
        if (in_array($context->getCurrentCustomerGroup()->getId(), $product->getBlockedCustomerGroupIds())) {
            return false;
        }

        $prices = $product->getPrices();
        if (empty($prices)) {
            return false;
        }

        if ($this->config->get('hideNoInStock') && !$product->isAvailable() && !$product->hasAvailableVariant()) {
            return false;
        }

        $ids = array_map(function (Struct\Category $category) {
            return $category->getId();
        }, $product->getCategories());

        return in_array($context->getShop()->getCategory()->getId(), $ids);
    }

    /**
     * @return bool
     */
    private function allowBuyInListing(ListProduct $product)
    {
        return !$product->hasConfigurator()
            && $product->isAvailable()
            && $product->getUnit()->getMinPurchase() <= 1
            && !$product->displayFromPrice();
    }

    /**
     * @param Struct\ListProduct[] $products
     *
     * @return array
     */
    private function getManufacturerCoverIds($products)
    {
        $ids = array_map(function (Struct\ListProduct $product) {
            return $product->getManufacturer()->getCoverId();
        }, $products);

        return array_filter($ids);
    }
}

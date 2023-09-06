<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
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
use Shopware\Bundle\StoreFrontBundle\Struct\Category;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\Manufacturer;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\Price;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware_Components_Config;

class ListProductService implements ListProductServiceInterface
{
    private ListProductGatewayInterface $productGateway;

    private MediaServiceInterface $mediaService;

    private CheapestPriceServiceInterface $cheapestPriceService;

    private GraduatedPricesServiceInterface $graduatedPricesService;

    private PriceCalculationServiceInterface $priceCalculationService;

    private MarketingServiceInterface $marketingService;

    private VoteServiceInterface $voteService;

    private CategoryServiceInterface $categoryService;

    private Shopware_Components_Config $config;

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
    public function get($number, ShopContextInterface $context)
    {
        $products = $this->getList([$number], $context);

        return array_shift($products);
    }

    /**
     * {@inheritdoc}
     */
    public function getList(array $numbers, ShopContextInterface $context)
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
            if (!\array_key_exists($number, $products)) {
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

            if ($product->getManufacturer() && isset($manufacturerCovers[$product->getManufacturer()->getCoverId()])) {
                $product->getManufacturer()->setCoverMedia($manufacturerCovers[$product->getManufacturer()->getCoverId()]);
            }

            $product->addAttribute('marketing', $this->marketingService->getProductAttribute($product));

            $this->priceCalculationService->calculateProduct($product, $context);

            if (!$this->isProductValid($product, $context)) {
                continue;
            }

            $product->setListingPrice($product->getCheapestUnitPrice());
            $product->setDisplayFromPrice(\count($product->getPrices()) > 1 || $product->hasDifferentPrices());
            $product->setAllowBuyInListing($this->allowBuyInListing($product));
            if ($this->config->get('calculateCheapestPriceWithMinPurchase') && $product->getCheapestPrice() instanceof Price) {
                $product->setListingPrice($product->getCheapestPrice());
            }
            $result[$number] = $product;
        }

        return $result;
    }

    /**
     * Checks if the provided product is allowed to display in the store front for
     * the provided context.
     */
    private function isProductValid(ListProduct $product, ShopContextInterface $context): bool
    {
        if (\in_array($context->getCurrentCustomerGroup()->getId(), $product->getBlockedCustomerGroupIds())) {
            return false;
        }

        $prices = $product->getPrices();
        if (empty($prices)) {
            return false;
        }

        if ($this->config->get('hideNoInStock') && !$product->isAvailable() && !$product->hasAvailableVariant()) {
            return false;
        }

        $ids = array_map(function (Category $category) {
            return $category->getId();
        }, $product->getCategories());

        return \in_array($context->getShop()->getCategory()->getId(), $ids);
    }

    private function allowBuyInListing(ListProduct $product): bool
    {
        return !$product->hasConfigurator()
            && $product->isAvailable()
            && $product->getUnit()
            && $product->getUnit()->getMinPurchase() <= 1
            && !$product->displayFromPrice();
    }

    /**
     * @param ListProduct[] $products
     *
     * @return array<int>
     */
    private function getManufacturerCoverIds(array $products): array
    {
        $ids = array_map(function (ListProduct $product) {
            if ($product->getManufacturer() instanceof Manufacturer) {
                return $product->getManufacturer()->getCoverId();
            }

            return null;
        }, $products);

        return array_filter($ids);
    }
}

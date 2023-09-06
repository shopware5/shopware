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

use Shopware\Bundle\SearchBundle\Condition\VariantCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\ProductSearchResult;
use Shopware\Bundle\StoreFrontBundle\Gateway\VariantCheapestPriceGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Service\PriceCalculationServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\VariantListingPriceServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\Price;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\PriceDiscount;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\PriceGroup;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\PriceRule;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware_Components_Config;

class VariantListingPriceService implements VariantListingPriceServiceInterface
{
    private VariantCheapestPriceGatewayInterface $variantCheapestPriceGateway;

    private PriceCalculationServiceInterface $priceCalculationService;

    private Shopware_Components_Config $config;

    public function __construct(
        VariantCheapestPriceGatewayInterface $variantCheapestPriceGateway,
        PriceCalculationServiceInterface $priceCalculationService,
        Shopware_Components_Config $config
    ) {
        $this->variantCheapestPriceGateway = $variantCheapestPriceGateway;
        $this->priceCalculationService = $priceCalculationService;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function updatePrices(Criteria $criteria, ProductSearchResult $result, ShopContextInterface $context)
    {
        $conditions = $criteria->getConditionsByClass(VariantCondition::class);

        $conditions = array_filter($conditions, function (VariantCondition $condition) {
            return $condition->expandVariants();
        });

        if (empty($conditions)) {
            return;
        }

        // executed if no price condition included in search request

        $this->loadPrices($criteria, $result, $context);
    }

    private function loadPrices(Criteria $criteria, ProductSearchResult $result, ShopContextInterface $context): void
    {
        $cheapestPriceData = $this->variantCheapestPriceGateway->getList($result->getProducts(), $context, $context->getCurrentCustomerGroup(), $criteria);

        foreach ($result->getProducts() as $product) {
            $number = $product->getNumber();

            if (!\array_key_exists($number, $cheapestPriceData)) {
                $cheapestPriceRule = $product->getPriceRules()[0];
                $displayFromPrice = $product->displayFromPrice();
            } else {
                $cheapestPriceRule = $cheapestPriceData[$number]['price'];
                $displayFromPrice = $cheapestPriceData[$number]['different_price_count'] > 1;
            }

            if ($product->isPriceGroupActive()) {
                $cheapestPriceRule = $this->calculatePriceGroupDiscounts($product, $cheapestPriceRule, $context);
            }

            $product->setCheapestPriceRule($cheapestPriceRule);
            $this->priceCalculationService->calculateProduct($product, $context);

            $product->setListingPrice($product->getCheapestUnitPrice());
            if ($this->config->get('calculateCheapestPriceWithMinPurchase') && $product->getCheapestPrice() instanceof Price) {
                $product->setListingPrice($product->getCheapestPrice());
            }

            $product->setDisplayFromPrice($displayFromPrice);
        }
    }

    private function calculatePriceGroupDiscounts(ListProduct $product, PriceRule $price, ShopContextInterface $context): PriceRule
    {
        if (!$product->isPriceGroupActive()) {
            return $price;
        }

        $discount = $this->getHighestQuantityDiscount($product, $context, $price->getFrom());

        if (!$discount instanceof PriceDiscount) {
            return $price;
        }
        $price->setPrice($price->getPrice() / 100 * (100 - $discount->getPercent()));

        return $price;
    }

    /**
     * Returns the highest price group discount for the provided product.
     *
     * The price groups are stored in the provided context object.
     * If the product has no configured price group or the price group has no discount defined for the
     * current customer group, the function returns null.
     */
    private function getHighestQuantityDiscount(ListProduct $product, ShopContextInterface $context, int $quantity): ?PriceDiscount
    {
        $priceGroups = $context->getPriceGroups();
        if (empty($priceGroups)) {
            return null;
        }

        $productPriceGroup = $product->getPriceGroup();
        if (!$productPriceGroup instanceof PriceGroup) {
            return null;
        }

        $id = $productPriceGroup->getId();
        if (!isset($priceGroups[$id])) {
            return null;
        }

        $priceGroup = $priceGroups[$id];

        $highest = null;
        foreach ($priceGroup->getDiscounts() as $discount) {
            if ($discount->getQuantity() > $quantity && !$this->config->get('useLastGraduationForCheapestPrice')) {
                continue;
            }

            if (!$highest) {
                $highest = $discount;
                continue;
            }

            if ($highest->getPercent() < $discount->getPercent()) {
                $highest = $discount;
            }
        }

        return $highest;
    }
}

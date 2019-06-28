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

use Shopware\Bundle\StoreFrontBundle\Gateway\CheapestPriceGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Service\CheapestPriceServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\Customer\Group;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\PriceDiscount;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\PriceRule;
use Shopware\Bundle\StoreFrontBundle\Struct\ProductContextInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class CheapestPriceService implements CheapestPriceServiceInterface
{
    /**
     * @var CheapestPriceGatewayInterface
     */
    private $cheapestPriceGateway;

    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    public function __construct(
        CheapestPriceGatewayInterface $cheapestPriceGateway,
        \Shopware_Components_Config $config
    ) {
        $this->cheapestPriceGateway = $cheapestPriceGateway;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function get(ListProduct $product, ProductContextInterface $context)
    {
        $cheapestPrices = $this->getList([$product], $context);

        return array_shift($cheapestPrices);
    }

    /**
     * {@inheritdoc}
     */
    public function getList($products, ProductContextInterface $context)
    {
        $group = $context->getCurrentCustomerGroup();

        $rules = $this->cheapestPriceGateway->getList($products, $context, $group);

        $prices = $this->buildPrices($products, $rules, $group);

        //check if one of the products have no assigned price within the prices variable.
        $fallbackProducts = array_filter(
            $products,
            function (BaseProduct $product) use ($prices) {
                return !array_key_exists($product->getNumber(), $prices);
            }
        );

        if (empty($fallbackProducts)) {
            return $this->calculatePriceGroupDiscounts($products, $prices, $context);
        }

        //if some product has no price, we have to load the fallback customer group prices for the fallbackProducts.
        $fallbackPrices = $this->cheapestPriceGateway->getList(
            $fallbackProducts,
            $context,
            $context->getFallbackCustomerGroup()
        );

        $fallbackPrices = $this->buildPrices(
            $fallbackProducts,
            $fallbackPrices,
            $context->getFallbackCustomerGroup()
        );

        $prices = $prices + $fallbackPrices;

        return $this->calculatePriceGroupDiscounts($products, $prices, $context);
    }

    /**
     * @param ListProduct[]                 $products
     * @param array<string, PriceRule|null> $prices
     * @param ProductContextInterface       $context
     *
     * @return PriceRule[]
     */
    private function calculatePriceGroupDiscounts($products, $prices, $context)
    {
        /** @var ListProduct $product */
        foreach ($products as $product) {
            if (!$product->isPriceGroupActive()) {
                continue;
            }

            $price = $prices[$product->getNumber()];

            if (!$price) {
                continue;
            }

            /** @var PriceRule $price */
            $discount = $this->getHighestQuantityDiscount($product, $context, $price->getFrom());

            if (!$discount) {
                continue;
            }
            $price->setPrice(
                $price->getPrice() / 100 * (100 - $discount->getPercent())
            );
        }

        return $prices;
    }

    /**
     * Helper function which iterates the products and builds a price array which indexed
     * with the product order number.
     *
     * @param BaseProduct[] $products
     * @param PriceRule[]   $priceRules
     *
     * @return array
     */
    private function buildPrices($products, array $priceRules, Group $group)
    {
        $prices = [];

        foreach ($products as $product) {
            $key = $product->getId();

            if (!array_key_exists($key, $priceRules) || empty($priceRules[$key])) {
                continue;
            }

            /** @var PriceRule $cheapestPrice */
            $cheapestPrice = $priceRules[$key];

            $cheapestPrice->setCustomerGroup($group);

            $prices[$product->getNumber()] = $cheapestPrice;
        }

        return $prices;
    }

    /**
     * Returns the highest price group discount for the provided product.
     *
     * The price groups are stored in the provided context object.
     * If the product has no configured price group or the price group has no discount defined for the
     * current customer group, the function returns null.
     *
     * @param int $quantity
     *
     * @return PriceDiscount|null
     */
    private function getHighestQuantityDiscount(ListProduct $product, ShopContextInterface $context, $quantity)
    {
        $priceGroups = $context->getPriceGroups();
        if (empty($priceGroups)) {
            return null;
        }

        $id = $product->getPriceGroup()->getId();
        if (!isset($priceGroups[$id])) {
            return null;
        }

        $priceGroup = $priceGroups[$id];

        /** @var PriceDiscount|null $highest */
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

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

namespace Shopware\Bundle\StoreFrontBundle\Price;

use Shopware\Bundle\StoreFrontBundle\Context\ShopContextInterface;
use Shopware\Bundle\StoreFrontBundle\CustomerGroup\CustomerGroup;
use Shopware\Bundle\StoreFrontBundle\Product\BaseProduct;
use Shopware\Bundle\StoreFrontBundle\Product\ListProduct;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class CheapestPriceService implements CheapestPriceServiceInterface
{
    /**
     * @var \Shopware\Bundle\StoreFrontBundle\Price\CheapestPriceGateway
     */
    private $cheapestPriceGateway;

    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    /**
     * @param \Shopware\Bundle\StoreFrontBundle\Price\CheapestPriceGateway $cheapestPriceGateway
     * @param \Shopware_Components_Config                                  $config
     */
    public function __construct(
        CheapestPriceGateway $cheapestPriceGateway,
        \Shopware_Components_Config $config
    ) {
        $this->cheapestPriceGateway = $cheapestPriceGateway;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function getList($products, ShopContextInterface $context)
    {
        $group = $context->getCurrentCustomerGroup();

        $rules = $this->cheapestPriceGateway->getList($products, $context->getTranslationContext(), $group);

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
            $context->getTranslationContext(),
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
     * @param ListProduct[]        $products
     * @param PriceRule[]          $prices
     * @param ShopContextInterface $context
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
     * @param CustomerGroup $group
     *
     * @return array
     */
    private function buildPrices($products, array $priceRules, CustomerGroup $group)
    {
        $prices = [];

        foreach ($products as $product) {
            $key = $product->getId();

            if (!array_key_exists($key, $priceRules) || empty($priceRules[$key])) {
                continue;
            }

            /** @var $cheapestPrice PriceRule */
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
     * @param ListProduct          $product
     * @param ShopContextInterface $context
     * @param $quantity
     *
     * @return null|\Shopware\Bundle\StoreFrontBundle\PriceGroup\PriceDiscount
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

        /** @var $highest \Shopware\Bundle\StoreFrontBundle\PriceGroup\PriceDiscount */
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

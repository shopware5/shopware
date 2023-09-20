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

use Shopware\Bundle\StoreFrontBundle\Gateway\GraduatedPricesGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Service\GraduatedPricesServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Customer\Group;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\PriceDiscount;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\PriceRule;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\Unit;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class GraduatedPricesService implements GraduatedPricesServiceInterface
{
    private GraduatedPricesGatewayInterface $graduatedPricesGateway;

    public function __construct(
        GraduatedPricesGatewayInterface $graduatedPricesGateway
    ) {
        $this->graduatedPricesGateway = $graduatedPricesGateway;
    }

    /**
     * {@inheritdoc}
     */
    public function get(ListProduct $product, ShopContextInterface $context)
    {
        $prices = $this->getList([$product], $context);

        return array_shift($prices);
    }

    /**
     * {@inheritdoc}
     */
    public function getList($products, ShopContextInterface $context)
    {
        $group = $context->getCurrentCustomerGroup();
        $specify = $this->graduatedPricesGateway->getList(
            $products,
            $context,
            $group
        );

        // iterates the passed prices and products and assign the product unit to the prices and the passed customer group
        $prices = $this->buildPrices(
            $products,
            $specify,
            $group
        );

        // check if one of the products have no assigned price within the prices variable.
        $fallbackProducts = array_filter(
            $products,
            function (ListProduct $product) use ($prices) {
                return !\array_key_exists($product->getNumber(), $prices);
            }
        );

        if (!empty($fallbackProducts)) {
            // if some product has no price, we have to load the fallback customer group prices for the fallbackProducts.
            $fallbackPrices = $this->graduatedPricesGateway->getList(
                $fallbackProducts,
                $context,
                $context->getFallbackCustomerGroup()
            );

            $fallbackPrices = $this->buildPrices(
                $fallbackProducts,
                $fallbackPrices,
                $context->getFallbackCustomerGroup()
            );

            // Do not use array_merge here. Since it will reindex the numbers of fallbackPrices.
            $prices += $fallbackPrices;
        }

        $priceGroups = $context->getPriceGroups();

        /*
         * If one of the products has a configured price group,
         * the graduated prices has to be build over the defined price group graduations.
         *
         * The price group discounts are defined with a percentage discount, which calculated
         * on the first graduated price of the product.
         */
        foreach ($products as $product) {
            if (!$product->isPriceGroupActive() || !$product->getPriceGroup()) {
                continue;
            }

            if (!isset($prices[$product->getNumber()])) {
                continue;
            }

            $priceGroupId = $product->getPriceGroup()->getId();
            if (!isset($priceGroups[$priceGroupId])) {
                continue;
            }

            $priceGroup = $priceGroups[$priceGroupId];

            $firstGraduation = array_shift($prices[$product->getNumber()]);
            if (!$firstGraduation instanceof PriceRule) {
                continue;
            }

            $prices[$product->getNumber()] = $this->buildDiscountGraduations(
                $firstGraduation,
                $context->getCurrentCustomerGroup(),
                $priceGroup->getDiscounts()
            );
        }

        return $prices;
    }

    /**
     * Helper function which builds the graduated prices
     * of a product for the passed price group discount array.
     *
     * This function is used to override the normal graduated prices
     * with a definition of the product price group discounts.
     *
     * @param PriceDiscount[] $discounts
     *
     * @return array<PriceRule>
     */
    private function buildDiscountGraduations(
        PriceRule $reference,
        Group $customerGroup,
        array $discounts
    ): array {
        $prices = [];

        $firstDiscount = $discounts[0];

        $previous = null;
        if ($firstDiscount->getQuantity() > 1) {
            $firstGraduation = clone $reference;
            $previous = $firstGraduation;

            $prices[] = $firstGraduation;
        }

        foreach ($discounts as $discount) {
            $rule = clone $reference;

            $percent = (100 - $discount->getPercent()) / 100;

            $price = $reference->getPrice() * $percent;

            $pseudo = $reference->getPseudoPrice();

            $rule->setPrice($price);

            $rule->setPseudoPrice($pseudo);

            $rule->setFrom($discount->getQuantity());

            $rule->setCustomerGroup($customerGroup);

            $rule->setTo(null);
            if ($previous) {
                $previous->setTo($rule->getFrom() - 1);
            }

            $previous = $rule;
            $prices[] = $rule;
        }

        return $prices;
    }

    /**
     * Helper function which iterates the products and builds a price array which indexed
     * with the product order number.
     *
     * @param ListProduct[]                   $products
     * @param array<string, array<PriceRule>> $priceRules
     *
     * @return array<string, array<PriceRule>>
     */
    private function buildPrices(array $products, array $priceRules, Group $group): array
    {
        $prices = [];

        foreach ($products as $product) {
            $key = $product->getNumber();

            if (!\array_key_exists($key, $priceRules) || empty($priceRules[$key])) {
                continue;
            }

            $productPrices = $priceRules[$key];

            foreach ($productPrices as $price) {
                if ($product->getUnit() instanceof Unit) {
                    $price->setUnit($product->getUnit());
                }
                $price->setCustomerGroup($group);
            }

            $prices[$key] = $productPrices;
        }

        return $prices;
    }
}

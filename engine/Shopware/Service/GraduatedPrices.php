<?php

namespace Shopware\Service;

use Shopware\Struct;
use Shopware\Gateway\DBAL as Gateway;

class GraduatedPrices
{
    /**
     * @var Gateway\GraduatedPrices
     */
    private $graduatedPricesGateway;

    /**
     * @var Gateway\PriceGroupDiscount
     */
    private $priceGroupDiscountGateway;

    /**
     * @param Gateway\GraduatedPrices $graduatedPricesGateway
     * @param Gateway\PriceGroupDiscount $priceGroupDiscountGateway
     */
    function __construct(
        Gateway\GraduatedPrices $graduatedPricesGateway,
        Gateway\PriceGroupDiscount $priceGroupDiscountGateway
    ) {
        $this->graduatedPricesGateway = $graduatedPricesGateway;
        $this->priceGroupDiscountGateway = $priceGroupDiscountGateway;
    }

    /**
     * Returns the graduated prices for a single product.
     *
     * The passed context is used for the customer group selection.
     *
     * If no prices defined for the Struct\Context::currentCustomerGroup
     * the function returns the fallback graduated prices for the
     * Struct\Context::fallbackCustomerGroup.
     *
     * The price returned as Struct\Product\PriceRule array, which
     * means that the prices are not calculated.
     *
     * The calculation can be called over the \Shopware\Service\PriceCalculation
     * service.
     *
     * @param Struct\ListProduct $product
     * @param \Shopware\Struct\Context $context
     * @return Struct\Product\PriceRule[]
     */
    public function get(Struct\ListProduct $product, Struct\Context $context)
    {
        $prices = $this->getList(array($product), $context);

        return array_shift($prices);
    }

    /**
     * Returns the graduated prices for all passed products.
     *
     * The passed array is indexed with the product id.
     *
     * The passed context is used for the customer group selection.
     *
     * If no prices defined for the Struct\Context::currentCustomerGroup
     * the function returns the fallback graduated prices for the
     * Struct\Context::fallbackCustomerGroup.
     *
     * The price returned as Struct\Product\PriceRule array, which
     * means that the prices are not calculated.
     *
     * The calculation can be called over the \Shopware\Service\PriceCalculation
     * service.
     *
     * @param Struct\ListProduct[] $products
     * @param \Shopware\Struct\Context $context
     * @return array returns an array of Struct\Product\PriceRule[]
     */
    public function getList(array $products, Struct\Context $context)
    {
        $group = $context->getCurrentCustomerGroup();
        $specify = $this->graduatedPricesGateway->getList(
            $products,
            $group
        );

        //iterates the passed prices and products and assign the product unit to the prices and the passed customer group
        $prices = $this->buildPrices(
            $products,
            $specify,
            $group
        );

        //check if one of the products have no assigned price within the prices variable.
        $fallbackProducts = array_filter(
            $products,
            function (Struct\ListProduct $product) use ($prices) {
                return !array_key_exists($product->getNumber(), $prices);
            }
        );

        if (!empty($fallbackProducts)) {
            //if some product has no price, we have to load the fallback customer group prices for the fallbackProducts.
            $fallbackPrices = $this->graduatedPricesGateway->getList(
                $fallbackProducts,
                $context->getFallbackCustomerGroup()
            );

            $fallbackPrices = $this->buildPrices(
                $fallbackProducts,
                $fallbackPrices,
                $context->getFallbackCustomerGroup()
            );

            $prices = array_merge($prices, $fallbackPrices);
        }

        /**
         * checks if one of the products has a configured price group and loads the different price group discounts.
         */
        $discounts = $this->priceGroupDiscountGateway->getProductsDiscounts(
            $products,
            $context->getCurrentCustomerGroup()
        );

        if (empty($discounts)) {
            return $prices;
        }

        /**
         * If one of the products has a configured price group,
         * the graduated prices has to be build over the defined price group graduations.
         *
         * The price group discounts are defined with a percentage discount, which calculated
         * on the first graduated price of the product.
         */
        foreach($products as $product) {
            $number = $product->getNumber();

            if (!array_key_exists($number, $discounts)) {
                continue;
            }

            $productDiscounts = $discounts[$number];

            $firstGraduation = $prices[$number][0];
            
            $prices[$number] = $this->buildDiscountGraduations(
                $product,
                $firstGraduation,
                $context->getCurrentCustomerGroup(),
                $productDiscounts
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
     * @param Struct\ListProduct $product
     * @param Struct\Product\PriceRule $reference
     * @param \Shopware\Struct\Customer\Group $customerGroup
     * @param Struct\Product\PriceDiscount[] $discounts
     * @return array
     */
    private function buildDiscountGraduations(
        Struct\ListProduct $product,
        Struct\Product\PriceRule $reference,
        Struct\Customer\Group $customerGroup,
        array $discounts
    ) {
        $prices = array();

        $firstDiscount = $discounts[0];

        /**@var $previous Struct\Product\PriceRule*/
        $previous = null;
        if ($firstDiscount->getQuantity() > 1) {
            $firstGraduation = clone $reference;
            $previous = $firstGraduation;

            $prices[] = $firstGraduation;
        }

        foreach($discounts as $discount) {
            $rule = clone $reference;

            $percent = (100 - $discount->getPercent() ) / 100;

            $price = $reference->getPrice() * $percent;

            $pseudo = $reference->getPseudoPrice() * $percent;

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
     * @param Struct\ListProduct[] $products
     * @param Struct\Product\PriceRule[] $priceRules
     * @param \Shopware\Struct\Customer\Group $group
     * @return array
     */
    private function buildPrices(array $products, array $priceRules, Struct\Customer\Group $group)
    {
        $prices = array();

        foreach ($products as $product) {
            $key = $product->getNumber();

            if (!array_key_exists($key, $priceRules) || empty($priceRules[$key])) {
                continue;
            }

            /**@var $productPrices Struct\Product\PriceRule[] */
            $productPrices = $priceRules[$key];

            foreach ($productPrices as $price) {
                $price->setUnit($product->getUnit());
                $price->setCustomerGroup($group);
            }

            $prices[$key] = $productPrices;
        }

        return $prices;
    }
}
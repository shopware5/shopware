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
     * @param Gateway\GraduatedPrices $graduatedPricesGateway
     */
    function __construct(Gateway\GraduatedPrices $graduatedPricesGateway)
    {
        $this->graduatedPricesGateway = $graduatedPricesGateway;
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
     * @return Struct\Product\PriceRule[]
     */
    public function getList(array $products, Struct\Context $context)
    {
        $specify = $this->graduatedPricesGateway->getList(
            $products,
            $context->getCurrentCustomerGroup()
        );

        $fallback = $this->graduatedPricesGateway->getList(
            $products,
            $context->getFallbackCustomerGroup()
        );

        $prices = array();

        foreach ($products as $product) {
            $group = $context->getCurrentCustomerGroup();

            /**@var $productPrices Struct\Product\PriceRule[] */
            $productPrices = $specify[$product->getVariantId()];

            if (empty($productPrices)) {
                $group = $context->getFallbackCustomerGroup();
                $productPrices = $fallback[$product->getVariantId()];
            }

            foreach ($productPrices as $price) {
                $price->setUnit($product->getUnit());
                $price->setCustomerGroup($group);
            }

            $prices[$product->getId()] = $productPrices;
        }

        return $prices;
    }


}
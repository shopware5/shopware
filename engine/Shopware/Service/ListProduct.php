<?php
/**
 * Created by PhpStorm.
 * User: oliverdenter
 * Date: 20.06.14
 * Time: 09:55
 */
namespace Shopware\Service;

use Shopware\Struct;

interface ListProduct
{
    /**
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Service\ListProduct::get()
     *
     * @param array $numbers
     * @param Struct\Context $context
     * @return Struct\ListProduct[] Indexed by the product order number.
     */
    public function getList(array $numbers, Struct\Context $context);

    /**
     * Returns a full \Shopware\Struct\ListProduct object.
     *
     * A full \Shopware\Struct\ListProduct is build over the following classes:
     * - \Shopware\Gateway\ListProduct      > Selects the base product data
     * - \Shopware\Service\Media            > Selects the cover
     * - \Shopware\Service\GraduatedPrices  > Selects the graduated prices
     * - \Shopware\Service\CheapestPrice    > Selects the cheapest price
     *
     * This data will be injected into the generated \Shopware\Struct\ListProduct object
     * and will be calculated through the \Shopware\Service\PriceCalculation class.
     *
     * @param string $number
     * @param Struct\Context $context
     * @return Struct\ListProduct
     */
    public function get($number, Struct\Context $context);
}

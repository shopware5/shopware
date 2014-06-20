<?php
/**
 * Created by PhpStorm.
 * User: oliverdenter
 * Date: 18.06.14
 * Time: 14:50
 */
namespace Shopware\Gateway;

use Shopware\Struct;


/**
 * @package Shopware\Gateway\DBAL
 */
interface ProductConfiguration
{
    /**
     * To get detailed information about the structure and content of the returned object,
     * please refer to the @see \Shopware\Gateway\DBAL\ProductConfiguration::get()
     *
     * @param Struct\ListProduct[] $products
     * @param \Shopware\Struct\Context $context
     * @return array Indexed by the product order number, each array element contains a Struct\Configurator\Group array.
     */
    public function getList(array $products, Struct\Context $context);

    /**
     * The \Shopware\Struct\Configurator\Group requires the following data:
     * - Configurator group data
     * - Only Configurator options which assigned to the product
     *
     * Required translation in the provided context language:
     * - Configurator groups
     * - Configurator options
     *
     * @param Struct\ListProduct $product
     * @param Struct\Context $context
     * @return Struct\Configurator\Group[]
     */
    public function get(Struct\ListProduct $product, Struct\Context $context);
}
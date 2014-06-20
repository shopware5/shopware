<?php
/**
 * Created by PhpStorm.
 * User: oliverdenter
 * Date: 20.06.14
 * Time: 09:55
 */
namespace Shopware\Service;

use Shopware\Struct;

interface Configurator
{
    /**
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Gateway\ProductConfiguration::get()
     *
     * @param Struct\ListProduct $product
     * @param Struct\Context $context
     * @return Struct\Configurator\Group[]
     */
    public function getProductConfiguration(Struct\ListProduct $product, Struct\Context $context);

    /**
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Gateway\ProductConfiguration::getList()
     *
     * @param Struct\ListProduct[] $products
     * @param Struct\Context $context
     * @return array Each array element contains a Struct\Configurator\Group[] array. The first level is indexed with the product number
     */
    public function getProductsConfigurations(array $products, Struct\Context $context);

    /**
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Gateway\Configurator::get()
     *
     * @param Struct\ListProduct $product
     * @param Struct\Context $context
     * @param array $selection
     * @return Struct\Configurator\Set
     */
    public function getProductConfigurator(Struct\ListProduct $product, Struct\Context $context, array $selection);
}
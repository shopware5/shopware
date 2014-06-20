<?php

namespace Shopware\Service\Core;

use Shopware\Gateway;
use Shopware\Service;
use Shopware\Struct;

class Configurator implements Service\Configurator
{
    /**
     * @var ProductConfiguration
     */
    private $productConfigurationGateway;

    /**
     * @var Gateway\Configurator
     */
    private $configuratorGateway;

    /**
     * @param Gateway\ProductConfiguration $productConfigurationGateway
     * @param Gateway\Configurator $configuratorGateway
     */
    function __construct(
        Gateway\ProductConfiguration $productConfigurationGateway,
        Gateway\Configurator $configuratorGateway
    ) {
        $this->configuratorGateway = $configuratorGateway;
        $this->productConfigurationGateway = $productConfigurationGateway;
    }

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
    public function getProductConfiguration(Struct\ListProduct $product, Struct\Context $context)
    {
        $configuration = $this->getProductsConfigurations(array($product), $context);
        return array_shift($configuration);
    }

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
    public function getProductsConfigurations(array $products, Struct\Context $context)
    {
        $configuration = $this->productConfigurationGateway->getList($products, $context);

        return $configuration;
    }

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
    public function getProductConfigurator(
        Struct\ListProduct $product,
        Struct\Context $context,
        array $selection
    ) {
        return $this->configuratorGateway->get($product, $context, $selection);
    }
}

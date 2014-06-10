<?php

namespace Shopware\Service;

use Shopware\Gateway\DBAL as Gateway;
use Shopware\Struct;

class Configurator
{
    /**
     * @var ProductConfiguration
     */
    private $productConfigurationGateway;

    /**
     * @var Gateway\Configurator
     */
    private $configuratorGateway;

    function __construct(
        Gateway\ProductConfiguration $productConfigurationGateway,
        Gateway\Configurator $configuratorGateway
    ) {
        $this->configuratorGateway = $configuratorGateway;
        $this->productConfigurationGateway = $productConfigurationGateway;
    }

    /**
     * @param ListProduct[] $products
     * @param \Shopware\Struct\Context $context
     * @return array Each array element contains a Struct\Configurator\Group[] array. The first level is indexed with the product number
     */
    public function getProductsConfigurations(array $products, Struct\Context $context)
    {
        $configuration = $this->productConfigurationGateway->getList($products);

        return $configuration;
    }

    /**
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
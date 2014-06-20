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
     * @inheritdoc
     */
    public function getProductConfiguration(Struct\ListProduct $product, Struct\Context $context)
    {
        $configuration = $this->getProductsConfigurations(array($product), $context);
        return array_shift($configuration);
    }

    /**
     * @inheritdoc
     */
    public function getProductsConfigurations(array $products, Struct\Context $context)
    {
        $configuration = $this->productConfigurationGateway->getList($products, $context);

        return $configuration;
    }

    /**
     * @inheritdoc
     */
    public function getProductConfigurator(
        Struct\ListProduct $product,
        Struct\Context $context,
        array $selection
    ) {
        return $this->configuratorGateway->get($product, $context, $selection);
    }
}

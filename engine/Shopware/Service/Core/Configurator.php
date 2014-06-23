<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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
namespace Shopware\Service\Core;

use Shopware\Gateway;
use Shopware\Service;
use Shopware\Struct;

/**
 * @package Shopware\Service\Core
 */
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

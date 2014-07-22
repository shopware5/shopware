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
namespace Shopware\Bundle\StoreFrontBundle\Service\Core;

use Shopware\Bundle\StoreFrontBundle\Gateway;
use Shopware\Bundle\StoreFrontBundle\Service;
use Shopware\Bundle\StoreFrontBundle\Struct;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\StoreFrontBundle\Service\Core
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ConfiguratorService implements Service\ConfiguratorServiceInterface
{
    /**
     * @var Gateway\ProductConfigurationGatewayInterface
     */
    private $productConfigurationGateway;

    /**
     * @var Gateway\ConfiguratorGatewayInterface
     */
    private $configuratorGateway;

    /**
     * @param Gateway\ProductConfigurationGatewayInterface $productConfigurationGateway
     * @param Gateway\ConfiguratorGatewayInterface $configuratorGateway
     */
    public function __construct(
        Gateway\ProductConfigurationGatewayInterface $productConfigurationGateway,
        Gateway\ConfiguratorGatewayInterface $configuratorGateway
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
    public function getProductsConfigurations($products, Struct\Context $context)
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

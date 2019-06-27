<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
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

class ConfiguratorService implements Service\ConfiguratorServiceInterface
{
    const CONFIGURATOR_TYPE_STANDARD = 0;
    const CONFIGURATOR_TYPE_SELECTION = 1;
    const CONFIGURATOR_TYPE_PICTURE = 2;

    /**
     * @var Gateway\ProductConfigurationGatewayInterface
     */
    private $productConfigurationGateway;

    /**
     * @var Gateway\ConfiguratorGatewayInterface
     */
    private $configuratorGateway;

    public function __construct(
        Gateway\ProductConfigurationGatewayInterface $productConfigurationGateway,
        Gateway\ConfiguratorGatewayInterface $configuratorGateway
    ) {
        $this->configuratorGateway = $configuratorGateway;
        $this->productConfigurationGateway = $productConfigurationGateway;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductConfiguration(Struct\BaseProduct $product, Struct\ShopContextInterface $context)
    {
        $configuration = $this->getProductsConfigurations([$product], $context);

        return array_shift($configuration);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductsConfigurations($products, Struct\ShopContextInterface $context)
    {
        return $this->productConfigurationGateway->getList($products, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductConfigurator(
        Struct\BaseProduct $product,
        Struct\ShopContextInterface $context,
        array $selection
    ) {
        $configurator = $this->configuratorGateway->get($product, $context);
        $combinations = $this->configuratorGateway->getProductCombinations($product);

        $media = [];
        if (((int) $configurator->getType()) === self::CONFIGURATOR_TYPE_PICTURE) {
            $media = $this->configuratorGateway->getConfiguratorMedia(
                $product,
                $context
            );
        }

        $onlyOneGroup = count($configurator->getGroups()) === self::CONFIGURATOR_TYPE_SELECTION;

        foreach ($configurator->getGroups() as $group) {
            $group->setSelected(
                isset($selection[$group->getId()])
            );

            foreach ($group->getOptions() as $option) {
                $option->setSelected(
                    in_array($option->getId(), $selection)
                );

                $isValid = $this->isCombinationValid(
                    $group,
                    $combinations[$option->getId()],
                    $selection
                );

                $option->setActive(
                    $isValid
                    || ($onlyOneGroup && isset($combinations[$option->getId()]))
                );

                if (isset($media[$option->getId()])) {
                    $option->setMedia(
                        $media[$option->getId()]
                    );
                }
            }
        }

        return $configurator;
    }

    /**
     * Checks if the passed combination is compatible with the provided customer configurator
     * selection.
     *
     * @param \Shopware\Bundle\StoreFrontBundle\Struct\Configurator\Group $group
     * @param array                                                       $combinations
     * @param array                                                       $selection
     *
     * @return bool
     */
    private function isCombinationValid(Struct\Configurator\Group $group, $combinations, $selection)
    {
        if (empty($combinations)) {
            return false;
        }

        foreach ($selection as $selectedGroup => $selectedOption) {
            if (!in_array($selectedOption, $combinations) && $selectedGroup !== $group->getId()) {
                return false;
            }
        }

        return true;
    }
}

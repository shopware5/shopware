<?php

declare(strict_types=1);
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

use Shopware\Bundle\StoreFrontBundle\Gateway\ConfiguratorGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway\ProductConfigurationGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ConfiguratorServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\Configurator\Group;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class ConfiguratorService implements ConfiguratorServiceInterface
{
    public const CONFIGURATOR_TYPE_STANDARD = 0;
    public const CONFIGURATOR_TYPE_SELECTION = 1;
    public const CONFIGURATOR_TYPE_PICTURE = 2;

    private ProductConfigurationGatewayInterface $productConfigurationGateway;

    private ConfiguratorGatewayInterface $configuratorGateway;

    public function __construct(
        ProductConfigurationGatewayInterface $productConfigurationGateway,
        ConfiguratorGatewayInterface $configuratorGateway
    ) {
        $this->productConfigurationGateway = $productConfigurationGateway;
        $this->configuratorGateway = $configuratorGateway;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductConfiguration(BaseProduct $product, ShopContextInterface $context)
    {
        $configuration = $this->getProductsConfigurations([$product], $context);

        return array_shift($configuration);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductsConfigurations($products, ShopContextInterface $context)
    {
        return $this->productConfigurationGateway->getList($products, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductConfigurator(
        BaseProduct $product,
        ShopContextInterface $context,
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

        $onlyOneGroup = \count($configurator->getGroups()) === self::CONFIGURATOR_TYPE_SELECTION;

        foreach ($configurator->getGroups() as $group) {
            $group->setSelected(
                isset($selection[$group->getId()])
            );

            foreach ($group->getOptions() as $option) {
                $option->setSelected(
                    \in_array($option->getId(), $selection)
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
     * @param array<string>|null $combinations
     * @param array<int, int>    $selection
     */
    private function isCombinationValid(Group $group, ?array $combinations, array $selection): bool
    {
        if (empty($combinations)) {
            return false;
        }

        foreach ($selection as $selectedGroup => $selectedOption) {
            if (!\in_array($selectedOption, $combinations) && $selectedGroup !== $group->getId()) {
                return false;
            }
        }

        return true;
    }
}

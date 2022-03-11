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

namespace Shopware\Tests\Functional\Bundle\StoreFrontBundle;

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\StoreFrontBundle\Gateway\ConfiguratorGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway\ProductConfigurationGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Service\Core\ConfiguratorService;
use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\Configurator\Group;
use Shopware\Bundle\StoreFrontBundle\Struct\Configurator\Option;
use Shopware\Bundle\StoreFrontBundle\Struct\Configurator\Set;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class ConfiguratorServiceTest extends TestCase
{
    private const GROUP_ID = 10;
    private const OPTION_ID_1 = 68;
    private const OPTION_ID_2 = 69;
    private const OPTION_ID_3 = 70;
    private const OPTION_ID_4 = 71;
    private const OPTION_ID_5 = 72;

    public function testGetProductConfigurator(): void
    {
        $configuratorSet = $this->createConfiguratorSet();
        $combinations = [
            self::OPTION_ID_1 => [0 => ''],
            self::OPTION_ID_2 => [0 => ''],
            self::OPTION_ID_3 => [0 => ''],
            self::OPTION_ID_4 => [0 => ''],
        ];
        $configuratorService = $this->createConfiguratorService($configuratorSet, $combinations);

        $product = new BaseProduct(171, 396, 'SW10171.1');
        $context = $this->createMock(ShopContextInterface::class);
        $set = $configuratorService->getProductConfigurator($product, $context, [self::GROUP_ID => self::OPTION_ID_1]);

        foreach ($set->getGroups() as $group) {
            foreach ($group->getOptions() as $option) {
                if ($option->getId() === self::OPTION_ID_5) {
                    static::assertFalse($option->getActive());
                } else {
                    static::assertTrue($option->getActive());
                }
            }
        }
    }

    /**
     * @param array<int, array<string>> $combinations
     */
    private function createConfiguratorService(Set $configuratorSet, array $combinations): ConfiguratorService
    {
        $productConfigurationGateway = $this->createMock(ProductConfigurationGatewayInterface::class);
        $configuratorGateway = $this->createMock(ConfiguratorGatewayInterface::class);
        $configuratorGateway->method('get')->willReturn($configuratorSet);
        $configuratorGateway->method('getProductCombinations')->willReturn($combinations);

        return new ConfiguratorService(
            $productConfigurationGateway,
            $configuratorGateway
        );
    }

    private function createConfiguratorSet(): Set
    {
        $set = new Set();
        $set->setId(22);
        $set->setType(ConfiguratorService::CONFIGURATOR_TYPE_STANDARD);
        $set->setGroups($this->createConfiguratorGroups());

        return $set;
    }

    /**
     * @return array<Group>
     */
    private function createConfiguratorGroups(): array
    {
        $group = new Group();
        $group->setId(self::GROUP_ID);
        $group->setOptions($this->createConfiguratorOptions());

        return [$group];
    }

    /**
     * @return array<Option>
     */
    private function createConfiguratorOptions(): array
    {
        $option0 = new Option();
        $option0->setId(self::OPTION_ID_1);
        $option1 = new Option();
        $option1->setId(self::OPTION_ID_2);
        $option2 = new Option();
        $option2->setId(self::OPTION_ID_3);
        $option3 = new Option();
        $option3->setId(self::OPTION_ID_4);
        $option4 = new Option();
        $option4->setId(self::OPTION_ID_5);

        return [$option0, $option1, $option2, $option3, $option4];
    }
}

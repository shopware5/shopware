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

namespace Shopware\Tests\Unit\Bundle\StoreFrontBundle\Service;

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\StoreFrontBundle\Gateway\ConfiguratorGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway\ProductConfigurationGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Service\Core\ConfiguratorService;
use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\Configurator\Group;
use Shopware\Bundle\StoreFrontBundle\Struct\Configurator\Option;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Models\Article\Configurator\Set;

class ConfiguratorServiceTest extends TestCase
{
    public function testNonActiveVariantsAreProperlySet(): void
    {
        $setMock = $this->createMocks();

        $productConfigurationGateway = $this->getMockForAbstractClass(ProductConfigurationGatewayInterface::class);
        $configurationGateway = $this->getMockForAbstractClass(ConfiguratorGatewayInterface::class);
        $configurationGateway->method('get')->willReturn($setMock);
        $configurationGateway->method('getAvailableConfigurations')->willReturn([
            11 => [[
                11, 22, 33, 35,
           ]],
            22 => [[
                22, 11, 12, 33, 35,
            ]],
            33 => [[
                33, 22, 11, 12,
            ]],
            12 => [[
                12, 22, 33,
            ]],
            35 => [[
                35, 11, 22,
            ]],
        ]);

        $shopContext = $this->getMockForAbstractClass(ShopContextInterface::class);
        $configuratorService = new ConfiguratorService($productConfigurationGateway, $configurationGateway);

        $baseProduct = new BaseProduct(1, 1, 'sw100');

        $configurator = $configuratorService->getProductConfigurator($baseProduct, $shopContext, [1 => 11, 2 => 22, 3 => 33]);

        $configuratorGroups = $configurator->getGroups();
        $configuratorGroupOptionsOne = $configuratorGroups[0]->getOptions();
        static::assertTrue($configuratorGroupOptionsOne[0]->isSelected());
        static::assertTrue($configuratorGroupOptionsOne[0]->getActive());

        static::assertFalse($configuratorGroupOptionsOne[1]->isSelected());
        static::assertTrue($configuratorGroupOptionsOne[1]->getActive());

        static::assertFalse($configuratorGroupOptionsOne[2]->getActive());

        $configuratorGroupOptionsTwo = $configuratorGroups[1]->getOptions();
        static::assertTrue($configuratorGroupOptionsTwo[0]->isSelected());
        static::assertTrue($configuratorGroupOptionsTwo[0]->getActive());
        static::assertFalse($configuratorGroupOptionsTwo[1]->getActive());
        static::assertFalse($configuratorGroupOptionsTwo[2]->getActive());

        $configuratorGroupOptionsThree = $configuratorGroups[2]->getOptions();
        static::assertTrue($configuratorGroupOptionsThree[0]->isSelected());
        static::assertTrue($configuratorGroupOptionsThree[0]->getActive());

        static::assertFalse($configuratorGroupOptionsThree[1]->getActive());
        static::assertTrue($configuratorGroupOptionsThree[2]->getActive());
    }

    public function testNonActiveVariantsAreProperlySetWithDifferentSelection(): void
    {
        $setMock = $this->createMocks();

        $productConfigurationGateway = $this->getMockForAbstractClass(ProductConfigurationGatewayInterface::class);
        $configurationGateway = $this->getMockForAbstractClass(ConfiguratorGatewayInterface::class);
        $configurationGateway->method('get')->willReturn($setMock);
        $configurationGateway->method('getAvailableConfigurations')->willReturn([
            11 => [[
                11, 22, 33, 35,
            ]],
            22 => [[
                22, 11, 12, 33, 35,
            ]],
            33 => [[
                33, 22, 11, 12,
            ]],
            12 => [[
                12, 22, 33,
            ]],
            35 => [[
                35, 11, 22,
            ]],
        ]);

        $shopContext = $this->getMockForAbstractClass(ShopContextInterface::class);
        $configuratorService = new ConfiguratorService($productConfigurationGateway, $configurationGateway);

        $baseProduct = new BaseProduct(1, 1, 'sw100');

        $configurator = $configuratorService->getProductConfigurator($baseProduct, $shopContext, [1 => 11, 2 => 22, 3 => 35]);

        $configuratorGroups = $configurator->getGroups();
        $configuratorGroupOptionsOne = $configuratorGroups[0]->getOptions();
        static::assertTrue($configuratorGroupOptionsOne[0]->isSelected());
        static::assertTrue($configuratorGroupOptionsOne[0]->getActive());

        static::assertFalse($configuratorGroupOptionsOne[1]->isSelected());
        static::assertFalse($configuratorGroupOptionsOne[1]->getActive());

        static::assertFalse($configuratorGroupOptionsOne[2]->getActive());

        $configuratorGroupOptionsTwo = $configuratorGroups[1]->getOptions();
        static::assertTrue($configuratorGroupOptionsTwo[0]->isSelected());
        static::assertTrue($configuratorGroupOptionsTwo[0]->getActive());
        static::assertFalse($configuratorGroupOptionsTwo[1]->getActive());
        static::assertFalse($configuratorGroupOptionsTwo[2]->getActive());

        $configuratorGroupOptionsThree = $configuratorGroups[2]->getOptions();
        static::assertFalse($configuratorGroupOptionsThree[0]->isSelected());
        static::assertTrue($configuratorGroupOptionsThree[0]->getActive());

        static::assertFalse($configuratorGroupOptionsThree[1]->getActive());
        static::assertTrue($configuratorGroupOptionsThree[2]->getActive());
        static::assertTrue($configuratorGroupOptionsThree[2]->isSelected());
    }

    public function testNonActiveVariantsAreProperlySetWithLessSelection(): void
    {
        $setMock = $this->createMocks();

        $productConfigurationGateway = $this->getMockForAbstractClass(ProductConfigurationGatewayInterface::class);
        $configurationGateway = $this->getMockForAbstractClass(ConfiguratorGatewayInterface::class);
        $configurationGateway->method('get')->willReturn($setMock);
        $configurationGateway->method('getAvailableConfigurations')->willReturn([
            11 => [[
                11, 22, 33, 35,
            ]],
            22 => [[
                22, 11, 12, 33, 35,
            ]],
            33 => [[
                33, 22, 11, 12,
            ]],
            12 => [[
                12, 22, 33,
            ]],
            35 => [[
                35, 11, 22,
            ]],
        ]);

        $shopContext = $this->getMockForAbstractClass(ShopContextInterface::class);
        $configuratorService = new ConfiguratorService($productConfigurationGateway, $configurationGateway);

        $baseProduct = new BaseProduct(1, 1, 'sw100');

        $configurator = $configuratorService->getProductConfigurator($baseProduct, $shopContext, [1 => 12, 2 => 22]);

        $configuratorGroups = $configurator->getGroups();
        $configuratorGroupOptionsOne = $configuratorGroups[0]->getOptions();
        static::assertFalse($configuratorGroupOptionsOne[0]->isSelected());
        static::assertTrue($configuratorGroupOptionsOne[0]->getActive());

        static::assertTrue($configuratorGroupOptionsOne[1]->isSelected());
        static::assertTrue($configuratorGroupOptionsOne[1]->getActive());

        static::assertFalse($configuratorGroupOptionsOne[2]->getActive());

        $configuratorGroupOptionsTwo = $configuratorGroups[1]->getOptions();
        static::assertTrue($configuratorGroupOptionsTwo[0]->isSelected());
        static::assertTrue($configuratorGroupOptionsTwo[0]->getActive());
        static::assertFalse($configuratorGroupOptionsTwo[1]->getActive());
        static::assertFalse($configuratorGroupOptionsTwo[2]->getActive());

        $configuratorGroupOptionsThree = $configuratorGroups[2]->getOptions();
        static::assertFalse($configuratorGroupOptionsThree[0]->isSelected());
        static::assertTrue($configuratorGroupOptionsThree[0]->getActive());

        static::assertFalse($configuratorGroupOptionsThree[1]->getActive());
        static::assertFalse($configuratorGroupOptionsThree[2]->getActive());
        static::assertFalse($configuratorGroupOptionsThree[2]->isSelected());
    }

    public function testNonActiveVariantsAreProperlySetWithFullUnavailable(): void
    {
        $setMock = $this->createMocks();

        $productConfigurationGateway = $this->getMockForAbstractClass(ProductConfigurationGatewayInterface::class);
        $configurationGateway = $this->getMockForAbstractClass(ConfiguratorGatewayInterface::class);
        $configurationGateway->method('get')->willReturn($setMock);
        $configurationGateway->method('getAvailableConfigurations')->willReturn([
            11 => [[
                11, 22, 35,
            ]],
            22 => [[
                22, 11, 12, 35,
            ]],
            12 => [[
                12, 22,
            ]],
            35 => [[
                35, 11, 22,
            ]],
        ]);

        $shopContext = $this->getMockForAbstractClass(ShopContextInterface::class);
        $configuratorService = new ConfiguratorService($productConfigurationGateway, $configurationGateway);

        $baseProduct = new BaseProduct(1, 1, 'sw100');

        $configurator = $configuratorService->getProductConfigurator($baseProduct, $shopContext, [1 => 11, 2 => 22]);

        $configuratorGroups = $configurator->getGroups();
        $configuratorGroupOptionsOne = $configuratorGroups[0]->getOptions();
        static::assertTrue($configuratorGroupOptionsOne[0]->isSelected());
        static::assertTrue($configuratorGroupOptionsOne[0]->getActive());

        static::assertFalse($configuratorGroupOptionsOne[1]->isSelected());
        static::assertTrue($configuratorGroupOptionsOne[1]->getActive());

        static::assertFalse($configuratorGroupOptionsOne[2]->getActive());

        $configuratorGroupOptionsTwo = $configuratorGroups[1]->getOptions();
        static::assertTrue($configuratorGroupOptionsTwo[0]->isSelected());
        static::assertTrue($configuratorGroupOptionsTwo[0]->getActive());
        static::assertFalse($configuratorGroupOptionsTwo[1]->getActive());
        static::assertFalse($configuratorGroupOptionsTwo[2]->getActive());

        $configuratorGroupOptionsThree = $configuratorGroups[2]->getOptions();
        static::assertFalse($configuratorGroupOptionsThree[0]->isSelected());
        static::assertFalse($configuratorGroupOptionsThree[0]->getActive());

        static::assertFalse($configuratorGroupOptionsThree[1]->getActive());
        static::assertTrue($configuratorGroupOptionsThree[2]->getActive());
        static::assertFalse($configuratorGroupOptionsThree[2]->isSelected());
    }

    public function testNoSelectionDoesStillDisableNotAvailableVariants(): void
    {
        $optionOne = new Option();
        $optionOne->setId(111);
        $optionTwo = new Option();
        $optionTwo->setId(222);

        $groupOne = $this->getMockBuilder(Group::class)->disableOriginalConstructor()->getMock();
        $groupOne->method('getId')->willReturn(111);
        $groupOne->method('setSelected')->willReturn(null);
        $groupOne->method('getOptions')->willReturn([
            $optionOne,
            $optionTwo,
        ]);

        $setMock = $this->getMockBuilder(Set::class)->disableOriginalConstructor()->getMock();
        $setMock->method('getGroups')->willReturn([
            $groupOne,
        ]);
        $setMock->method('getType')->willReturn(ConfiguratorService::CONFIGURATOR_TYPE_STANDARD);

        $productConfigurationGateway = $this->getMockForAbstractClass(ProductConfigurationGatewayInterface::class);
        $configurationGateway = $this->getMockForAbstractClass(ConfiguratorGatewayInterface::class);
        $configurationGateway->method('get')->willReturn($setMock);
        $configurationGateway->method('getAvailableConfigurations')->willReturn([111 => [[111]]]);

        $shopContext = $this->getMockForAbstractClass(ShopContextInterface::class);
        $configuratorService = new ConfiguratorService($productConfigurationGateway, $configurationGateway);

        $baseProduct = new BaseProduct(1, 1, 'sw100');

        $configurator = $configuratorService->getProductConfigurator($baseProduct, $shopContext, []);

        $configuratorGroups = $configurator->getGroups();
        $configuratorGroupOptionsOne = $configuratorGroups[0]->getOptions();

        static::assertTrue($configuratorGroupOptionsOne[0]->getActive());
        static::assertFalse($configuratorGroupOptionsOne[1]->getActive());
    }

    public function testNoConfigurationsAreAvailable(): void
    {
        $setMock = $this->createMocks();

        $productConfigurationGateway = $this->getMockForAbstractClass(ProductConfigurationGatewayInterface::class);
        $configurationGateway = $this->getMockForAbstractClass(ConfiguratorGatewayInterface::class);
        $configurationGateway->method('get')->willReturn($setMock);
        $configurationGateway->method('getAvailableConfigurations')->willReturn([]);

        $shopContext = $this->getMockForAbstractClass(ShopContextInterface::class);
        $configuratorService = new ConfiguratorService($productConfigurationGateway, $configurationGateway);

        $baseProduct = new BaseProduct(1, 1, 'sw100');

        $configurator = $configuratorService->getProductConfigurator($baseProduct, $shopContext, [1 => 11, 2 => 22]);

        $configuratorGroups = $configurator->getGroups();
        $configuratorGroupOptionsOne = $configuratorGroups[0]->getOptions();
        static::assertTrue($configuratorGroupOptionsOne[0]->isSelected());
        static::assertFalse($configuratorGroupOptionsOne[0]->getActive());

        static::assertFalse($configuratorGroupOptionsOne[1]->isSelected());
        static::assertFalse($configuratorGroupOptionsOne[1]->getActive());
        static::assertFalse($configuratorGroupOptionsOne[2]->getActive());

        $configuratorGroupOptionsTwo = $configuratorGroups[1]->getOptions();
        static::assertTrue($configuratorGroupOptionsTwo[0]->isSelected());
        static::assertFalse($configuratorGroupOptionsTwo[0]->getActive());
        static::assertFalse($configuratorGroupOptionsTwo[1]->getActive());
        static::assertFalse($configuratorGroupOptionsTwo[2]->getActive());

        $configuratorGroupOptionsThree = $configuratorGroups[2]->getOptions();
        static::assertFalse($configuratorGroupOptionsThree[0]->isSelected());
        static::assertFalse($configuratorGroupOptionsThree[0]->getActive());

        static::assertFalse($configuratorGroupOptionsThree[1]->getActive());
        static::assertFalse($configuratorGroupOptionsThree[2]->getActive());
        static::assertFalse($configuratorGroupOptionsThree[2]->isSelected());
    }

    /**
     * @return Option[]
     */
    private function createOptions(): array
    {
        $optionOne = new Option();
        $optionOne->setId(11);

        $optionOneTwo = new Option();
        $optionOneTwo->setId(12);

        $optionOneThree = new Option();
        $optionOneThree->setId(13);

        $optionTwo = new Option();
        $optionTwo->setId(22);

        $optionTwoThree = new Option();
        $optionTwoThree->setId(23);

        $optionTwoFour = new Option();
        $optionTwoFour->setId(24);

        $optionThree = new Option();
        $optionThree->setId(33);

        $optionThreeFour = new Option();
        $optionThreeFour->setId(34);

        $optionThreeFive = new Option();
        $optionThreeFive->setId(35);

        return [$optionOne, $optionOneTwo, $optionOneThree, $optionTwo, $optionTwoThree, $optionTwoFour, $optionThree, $optionThreeFour, $optionThreeFive];
    }

    private function createMocks(): Set
    {
        [$optionOne, $optionOneTwo, $optionOneThree, $optionTwo, $optionTwoThree, $optionTwoFour, $optionThree, $optionThreeFour, $optionThreeFive] = $this->createOptions();

        $groupOne = $this->getMockBuilder(Group::class)->disableOriginalConstructor()->getMock();
        $groupOne->method('getId')->willReturn(1);
        $groupOne->method('setSelected')->willReturn(null);
        $groupOne->method('getOptions')->willReturn([
            $optionOne,
            $optionOneTwo,
            $optionOneThree,
        ]);

        $groupTwo = $this->getMockBuilder(Group::class)->disableOriginalConstructor()->getMock();
        $groupTwo->method('getId')->willReturn(2);
        $groupTwo->method('setSelected')->willReturn(null);
        $groupTwo->method('getOptions')->willReturn([
            $optionTwo,
            $optionTwoThree,
            $optionTwoFour,
        ]);

        $groupThree = $this->getMockBuilder(Group::class)->disableOriginalConstructor()->getMock();
        $groupThree->method('getId')->willReturn(3);

        $groupThree->method('setSelected')->willReturn(null);
        $groupThree->method('getOptions')->willReturn([
            $optionThree,
            $optionThreeFour,
            $optionThreeFive,
        ]);

        $setMock = $this->getMockBuilder(Set::class)->disableOriginalConstructor()->getMock();
        $setMock->method('getGroups')->willReturn([
            $groupOne,
            $groupTwo,
            $groupThree,
        ]);
        $setMock->method('getType')->willReturn(ConfiguratorService::CONFIGURATOR_TYPE_STANDARD);

        return $setMock;
    }
}

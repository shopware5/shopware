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

namespace Shopware\Tests\Unit\Bundle\OrderBundle;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\OrderBundle\Service\ShippingCostService;
use Shopware\Models\Dispatch\Dispatch;

class ShippingConstServiceTest extends TestCase
{
    public function testErrorIsThrownWithNotExistingTypeStrings(): void
    {
        $shippingConstService = new ShippingCostService($this->getConnectionMock());

        $this->expectExceptionMessage('Shipping calculation type "99" not supported');
        $shippingConstService->getShippingCostMultiplier(99, [], []);
    }

    public function testDefaultShippingCost(): void
    {
        $shippingConstService = new ShippingCostService($this->getConnectionMock());

        static::assertSame(1.556, $shippingConstService->getShippingCostMultiplier(Dispatch::CALCULATION_WEIGHT, ['weight' => '1.55555'], []));
    }

    public function testPriceShippingCost(): void
    {
        $shippingConstService = new ShippingCostService($this->getConnectionMock());

        static::assertSame(1.56, $shippingConstService->getShippingCostMultiplier(Dispatch::CALCULATION_PRICE, ['amount' => '1.55555'], []));
    }

    public function testNumberOfProductsPriceShippingCost(): void
    {
        $shippingConstService = new ShippingCostService($this->getConnectionMock());

        static::assertSame(2.0, $shippingConstService->getShippingCostMultiplier(Dispatch::CALCULATION_NUMBER_OF_PRODUCTS, ['count_article' => '2.2'], []));
    }

    public function testCustomPriceShippingCost(): void
    {
        $shippingConstService = new ShippingCostService($this->getConnectionMock());

        static::assertSame(2.22, $shippingConstService->getShippingCostMultiplier(Dispatch::CALCULATION_CUSTOM, ['calculation_value_1' => '2.222'], ['id' => '1']));
    }

    private function getConnectionMock(): Connection
    {
        return $this->getMockBuilder(Connection::class)->disableOriginalConstructor()->getMock();
    }
}

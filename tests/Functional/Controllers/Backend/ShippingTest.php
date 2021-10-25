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

namespace Shopware\Tests\Functional\Controllers\Backend;

use Enlight_Components_Test_Controller_TestCase as ControllerTestCase;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class ShippingTest extends ControllerTestCase
{
    use DatabaseTransactionBehaviour;

    protected function setUp(): void
    {
        parent::setUp();

        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        Shopware()->Plugins()->Backend()->Auth()->setNoAcl();
    }

    public function testCreateDispatch(): void
    {
        $this->Request()->setParams([
            'name' => 'TestShipping',
            'type' => 0,
            'description' => '',
            'comment' => '',
            'active' => 1,
            'position' => '1',
            'calculation' => 1,
            'surchargeCalculation' => 3,
            'taxCalculation' => 'Auto-Ermittlung',
            'bindLastStock' => 0,
            // Values which are cleaned-up
            'multiShopId' => 0,
            'customerGroupId' => 0,
            'bindTimeFrom' => '00:00',
            'bindTimeTo' => '',
            'bindInStock' => null,
            'bindWeekdayFrom' => '1',
            'bindWeekdayTo' => '5',
            'bindWeightFrom' => 0.0,
            'bindWeightTo' => 1.2,
            'bindPriceFrom' => 0.1,
            'bindPriceTo' => null,
            'bindSql' => 'WHERE test=1',
            'calculationSql' => 'WHERE test=2',
        ]);

        $this->dispatch('backend/shipping/createDispatch');
        $assignedVariables = $this->View()->getAssign();

        static::assertTrue($assignedVariables['success']);
        $shippingCost = $assignedVariables['data'];
        static::assertNotEmpty($shippingCost);
        static::assertSame('TestShipping', $shippingCost['name']);

        static::assertNull($shippingCost['multiShopId']);
        static::assertNull($shippingCost['customerGroupId']);
        static::assertSame(0, $shippingCost['bindTimeFrom']);
        static::assertNull($shippingCost['bindTimeTo']);
        static::assertNull($shippingCost['bindInStock']);
        static::assertSame('1', $shippingCost['bindWeekdayFrom']);
        static::assertSame('5', $shippingCost['bindWeekdayTo']);
        static::assertNull($shippingCost['bindWeightFrom']);
        static::assertSame(1.2, $shippingCost['bindWeightTo']);
        static::assertSame(0.1, $shippingCost['bindPriceFrom']);
        static::assertNull($shippingCost['bindPriceTo']);
        static::assertSame('WHERE test=1', $shippingCost['bindSql']);
        static::assertSame('WHERE test=2', $shippingCost['calculationSql']);
    }
}

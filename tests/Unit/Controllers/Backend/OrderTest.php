<?php

declare(strict_types=1);
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Tests\Unit\Controllers\Backend;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use Shopware_Controllers_Backend_Order;

class OrderTest extends TestCase
{
    private Shopware_Controllers_Backend_Order $controller;

    private ReflectionMethod $method;

    protected function setUp(): void
    {
        $this->controller = $this->createPartialMock(Shopware_Controllers_Backend_Order::class, []);
        $this->method = (new ReflectionClass($this->controller))->getMethod('resolveSortParameter');
        $this->method->setAccessible(true);
    }

    public function testSortByNonePrefixedColumn(): void
    {
        $sorts = [
            ['property' => 'orderTime', 'direction' => 'ASC'],
        ];

        static::assertSame(
            [
                ['property' => 'orders.orderTime', 'direction' => 'ASC'],
            ],
            $this->method->invokeArgs($this->controller, [$sorts])
        );
    }

    public function testSortByMultipleColumnsWithoutPrefix(): void
    {
        $sorts = [
            ['property' => 'orderTime', 'direction' => 'ASC'],
            ['property' => 'active', 'direction' => 'ASC'],
        ];

        static::assertSame(
            [
                ['property' => 'orders.orderTime', 'direction' => 'ASC'],
                ['property' => 'orders.active', 'direction' => 'ASC'],
            ],
            $this->method->invokeArgs($this->controller, [$sorts])
        );
    }

    public function testResolveSortParametersKeepsDirection(): void
    {
        $sorts = [
            ['property' => 'orderTime', 'direction' => 'DESC'],
            ['property' => 'active', 'direction' => 'DESC'],
            ['property' => 'customerId', 'direction' => 'ASC'],
        ];

        static::assertSame(
            [
                ['property' => 'orders.orderTime', 'direction' => 'DESC'],
                ['property' => 'orders.active', 'direction' => 'DESC'],
                ['property' => 'orders.customerId', 'direction' => 'ASC'],
            ],
            $this->method->invokeArgs($this->controller, [$sorts])
        );
    }

    public function testResolveFunctionsKeepsPrefixedProperties(): void
    {
        $sorts = [
            ['property' => 'customer.name', 'direction' => 'DESC'],
            ['property' => 'customer.email', 'direction' => 'DESC'],
            ['property' => 'billing.countryId', 'direction' => 'ASC'],
        ];

        static::assertSame(
            [
                ['property' => 'customer.name', 'direction' => 'DESC'],
                ['property' => 'customer.email', 'direction' => 'DESC'],
                ['property' => 'billing.countryId', 'direction' => 'ASC'],
            ],
            $this->method->invokeArgs($this->controller, [$sorts])
        );
    }

    public function testCustomerNameColumnResolvedToBillingNames(): void
    {
        $sorts = [
            ['property' => 'customerName', 'direction' => 'DESC'],
        ];

        static::assertSame(
            [
                ['property' => 'billing.lastName', 'direction' => 'DESC'],
                ['property' => 'billing.firstName', 'direction' => 'DESC'],
                ['property' => 'billing.company', 'direction' => 'DESC'],
            ],
            $this->method->invokeArgs($this->controller, [$sorts])
        );
    }

    public function testCustomerEmailAliasResolvedToAssociatedColumn(): void
    {
        $sorts = [
            ['property' => 'customerEmail', 'direction' => 'DESC'],
        ];

        static::assertSame(
            [
                ['property' => 'customer.email', 'direction' => 'DESC'],
            ],
            $this->method->invokeArgs($this->controller, [$sorts])
        );
    }
}

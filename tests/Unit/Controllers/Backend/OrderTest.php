<?php

namespace Shopware\tests\Unit\Controllers\Backend;

use Shopware_Controllers_Backend_Order;

class OrderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $controller;

    /**
     * @var \ReflectionMethod
     */
    private $method;

    protected function setUp()
    {
        $this->controller = $this->createPartialMock(Shopware_Controllers_Backend_Order::class, []);
        $class = new \ReflectionClass($this->controller);
        $this->method = $class->getMethod('resolveSortParameter');
        $this->method->setAccessible(true);
    }
    
    public function testSortByNonePrefixedColumn()
    {
        $sorts = [
            ['property' => 'orderTime', 'direction' => 'ASC']
        ];

        $this->assertSame(
            [
                ['property' => 'orders.orderTime', 'direction' => 'ASC']
            ],
            $this->method->invokeArgs($this->controller, [$sorts])
        );
    }

    public function testSortByMultipleColumnsWithoutPrefix()
    {
        $sorts = [
            ['property' => 'orderTime', 'direction' => 'ASC'],
            ['property' => 'active', 'direction' => 'ASC']
        ];

        $this->assertSame(
            [
                ['property' => 'orders.orderTime', 'direction' => 'ASC'],
                ['property' => 'orders.active', 'direction' => 'ASC']
            ],
            $this->method->invokeArgs($this->controller, [$sorts])
        );
    }

    public function testResolveSortParametersKeepsDirection()
    {
        $sorts = [
            ['property' => 'orderTime', 'direction' => 'DESC'],
            ['property' => 'active', 'direction' => 'DESC'],
            ['property' => 'customerId', 'direction' => 'ASC'],
        ];

        $this->assertSame(
            [
                ['property' => 'orders.orderTime', 'direction' => 'DESC'],
                ['property' => 'orders.active', 'direction' => 'DESC'],
                ['property' => 'orders.customerId', 'direction' => 'ASC'],
            ],
            $this->method->invokeArgs($this->controller, [$sorts])
        );
    }

    public function testResolveFunctionsKeepsPrefixedProperties()
    {
        $sorts = [
            ['property' => 'customer.name', 'direction' => 'DESC'],
            ['property' => 'customer.email', 'direction' => 'DESC'],
            ['property' => 'billing.countryId', 'direction' => 'ASC'],
        ];

        $this->assertSame(
            [
                ['property' => 'customer.name', 'direction' => 'DESC'],
                ['property' => 'customer.email', 'direction' => 'DESC'],
                ['property' => 'billing.countryId', 'direction' => 'ASC'],
            ],
            $this->method->invokeArgs($this->controller, [$sorts])
        );
    }

    public function testCustomerNameColumnResolvedToBillingNames()
    {
        $sorts = [
            ['property' => 'customerName', 'direction' => 'DESC'],
        ];

        $this->assertSame(
            [
                ['property' => 'billing.company', 'direction' => 'DESC'],
                ['property' => 'billing.lastName', 'direction' => 'DESC'],
                ['property' => 'billing.firstName', 'direction' => 'DESC']
            ],
            $this->method->invokeArgs($this->controller, [$sorts])
        );
    }


    public function testCustomerEmailAliasResolvedToAssociatedColumn()
    {
        $sorts = [
            ['property' => 'customerEmail', 'direction' => 'DESC'],
        ];

        $this->assertSame(
            [
                ['property' => 'customer.email', 'direction' => 'DESC']
            ],
            $this->method->invokeArgs($this->controller, [$sorts])
        );
    }
}

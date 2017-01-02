<?php

namespace Shopware\Tests\Functional\Bundle\CustomerSearchBundle\ConditionHandler;

use Shopware\Bundle\CustomerSearchBundle\Condition\HasOrderCountCondition;
use Shopware\Bundle\CustomerSearchBundle\Criteria;
use Shopware\Tests\Functional\Bundle\CustomerSearchBundle\TestCase;

class HasOrderCountConditionHandlerTest extends TestCase
{
    public function testCustomerHasOneOrder()
    {
        $criteria = new Criteria();
        $criteria->addCondition(new HasOrderCountCondition(1));

        $this->search(
            $criteria,
            ['number1'],
            [
                [
                    'email' => 'test1@example.com',
                    'number' => 'number1',
                    'orders' => [
                        ['ordernumber' => '123', 'status' => 2]
                    ]
                ],
                [
                    'email' => 'test2@example.com',
                    'number' => 'number2'
                ]
            ]
        );
    }

    public function testMultipleCustomerHasOrders()
    {
        $criteria = new Criteria();
        $criteria->addCondition(new HasOrderCountCondition(2));

        $this->search(
            $criteria,
            ['number1', 'number2'],
            [
                [
                    'email' => 'test1@example.com',
                    'number' => 'number1',
                    'orders' => [
                        ['ordernumber' => '1', 'status' => 2],
                        ['ordernumber' => '2', 'status' => 2],
                    ]
                ],
                [
                    'email' => 'test2@example.com',
                    'number' => 'number2',
                    'orders' => [
                        ['ordernumber' => '4', 'status' => 2],
                        ['ordernumber' => '5', 'status' => 2],
                        ['ordernumber' => '6', 'status' => 2]
                    ]
                ],
                [
                    'email' => 'test3@example.com',
                    'number' => 'number3',
                    'orders' => [
                        ['ordernumber' => '7', 'status' => 2]
                    ]
                ]
            ]
        );
    }
}
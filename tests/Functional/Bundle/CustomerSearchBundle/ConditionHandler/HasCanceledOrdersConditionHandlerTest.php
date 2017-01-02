<?php

namespace Shopware\Tests\Functional\Bundle\CustomerSearchBundle\ConditionHandler;

use Shopware\Bundle\CustomerSearchBundle\Condition\HasCanceledOrdersCondition;
use Shopware\Bundle\CustomerSearchBundle\Criteria;
use Shopware\Tests\Functional\Bundle\CustomerSearchBundle\TestCase;

class HasCanceledOrdersConditionHandlerTest extends TestCase
{
    public function testWithOneCanceledOrder()
    {
        $criteria = new Criteria();
        $criteria->addCondition(new HasCanceledOrdersCondition());

        $this->search(
            $criteria,
            ['number1'],
            [
                [
                    'email' => 'test1@example.com',
                    'number' => 'number1',
                    'orders' => [
                        ['status' => -1, 'ordernumber' => '1']
                    ]
                ],
                [
                    'email' => 'test2@example.com',
                    'number' => 'number2',
                    'orders' => [
                        ['status' => 2, 'ordernumber' => '2']
                    ]
                ]
            ]
        );
    }

    public function testWithMultipleOrders()
    {
        $criteria = new Criteria();
        $criteria->addCondition(new HasCanceledOrdersCondition());

        $this->search(
            $criteria,
            ['number1', 'number2'],
            [
                [
                    'email' => 'test1@example.com',
                    'number' => 'number1',
                    'orders' => [
                        ['status' => 2, 'ordernumber' => '1'],
                        ['status' => 12, 'ordernumber' => '2'],
                        ['status' => 4, 'ordernumber' => '3'],
                        ['status' => 0, 'ordernumber' => '4'],
                        ['status' => -1, 'ordernumber' => '10'],
                        ['status' => -1, 'ordernumber' => '5']
                    ]
                ],
                [
                    'email' => 'test2@example.com',
                    'number' => 'number2',
                    'orders' => [
                        ['status' => -1, 'ordernumber' => '6']
                    ]
                ],
                [
                    'email' => 'test3@example.com',
                    'number' => 'number3',
                    'orders' => [
                        ['status' => 3, 'ordernumber' => '6'],
                        ['status' => 4, 'ordernumber' => '7'],
                        ['status' => 5, 'ordernumber' => '8']
                    ]
                ]
            ]
        );
    }
}
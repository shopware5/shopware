<?php

namespace Shopware\Tests\Functional\Bundle\CustomerSearchBundle\ConditionHandler;

use Shopware\Bundle\CustomerSearchBundle\Condition\HasTotalOrderAmountCondition;
use Shopware\Bundle\CustomerSearchBundle\Criteria;
use Shopware\Tests\Functional\Bundle\CustomerSearchBundle\TestCase;

class HasTotalOrderAmountConditionHandlerTest extends TestCase
{
    public function testWithSingleCustomer()
    {
        $criteria = new Criteria();
        $criteria->addCondition(new HasTotalOrderAmountCondition(600));

        $this->search(
            $criteria,
            ['number1'],
            [
                [
                    'email' => 'test1@example.com',
                    'number' => 'number1',
                    'orders' => [
                        ['invoice_amount' => 100, 'status' => 2, 'ordernumber' => '1'],
                        ['invoice_amount' => 200, 'status' => 2, 'ordernumber' => '2'],
                        ['invoice_amount' => 300, 'status' => 2, 'ordernumber' => '3']
                    ]
                ],
                [
                    'email' => 'test2@example.com',
                    'number' => 'number2',
                    'orders' => [
                        ['invoice_amount' => 100, 'status' => 2, 'ordernumber' => '4'],
                        ['invoice_amount' => 200, 'status' => 2, 'ordernumber' => '5'],
                        ['invoice_amount' => 250, 'status' => 2, 'ordernumber' => '6']
                    ]
                ]
            ]
        );
    }

    public function testWithCanceledOrders()
    {
        $criteria = new Criteria();
        $criteria->addCondition(new HasTotalOrderAmountCondition(200));

        $this->search(
            $criteria,
            ['number1'],
            [
                [
                    'email' => 'test1@example.com',
                    'number' => 'number1',
                    'orders' => [
                        ['invoice_amount' => 100, 'status' => 2, 'ordernumber' => '1'],
                        ['invoice_amount' => 200, 'status' => 2, 'ordernumber' => '2'],
                        ['invoice_amount' => 300, 'status' => 2, 'ordernumber' => '3']
                    ]
                ],
                [
                    'email' => 'test2@example.com',
                    'number' => 'number2',
                    'orders' => [
                        ['invoice_amount' => 100, 'status' =>  2, 'ordernumber' => '4'],
                        ['invoice_amount' => 200, 'status' => -1, 'ordernumber' => '5'],
                        ['invoice_amount' => 500, 'status' => -1, 'ordernumber' => '6']
                    ]
                ]
            ]
        );
    }
}
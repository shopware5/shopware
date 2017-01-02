<?php

namespace Shopware\Tests\Functional\Bundle\CustomerSearchBundle\ConditionHandler;

use Shopware\Bundle\CustomerSearchBundle\Condition\OrderedAtWeekdayCondition;
use Shopware\Bundle\CustomerSearchBundle\Criteria;
use Shopware\Tests\Functional\Bundle\CustomerSearchBundle\TestCase;

class OrderedAtWeekdayConditionHandlerTest extends TestCase
{
    public function testSingleDay()
    {
        $criteria = new Criteria();
        $criteria->addCondition(
            new OrderedAtWeekdayCondition([
                'monday'
            ])
        );

        $this->search(
            $criteria,
            ['number1', 'number3'],
            [
                [
                    'email' => 'test1@example.com',
                    'number' => 'number1',
                    'orders' => [
                        ['ordernumber' => '1', 'ordertime' => '2016-12-26', 'status' => 2]
                    ]
                ],
                [
                    'email' => 'test2@example.com',
                    'number' => 'number2',
                    'orders' => [
                        ['ordernumber' => '2', 'ordertime' => '2016-12-13', 'status' => 2]
                    ]
                ],
                [
                    'email' => 'test3@example.com',
                    'number' => 'number3',
                    'orders' => [
                        ['ordernumber' => '3', 'ordertime' => '2016-12-27', 'status' => 2],
                        ['ordernumber' => '4', 'ordertime' => '2016-12-19', 'status' => 2],
                    ]
                ]
            ]
        );
    }

    public function testDifferentDays()
    {
        $criteria = new Criteria();
        $criteria->addCondition(
            new OrderedAtWeekdayCondition([
                'monday', 'tuesday'
            ])
        );

        $this->search(
            $criteria,
            ['number1', 'number3'],
            [
                [
                    'email' => 'test1@example.com',
                    'number' => 'number1',
                    'orders' => [
                        ['ordernumber' => '1', 'ordertime' => '2016-12-05', 'status' => 2]
                    ]
                ],
                [
                    'email' => 'test2@example.com',
                    'number' => 'number2',
                    'orders' => [
                        ['ordernumber' => '2', 'ordertime' => '2016-12-14', 'status' => 2]
                    ]
                ],
                [
                    'email' => 'test3@example.com',
                    'number' => 'number3',
                    'orders' => [
                        ['ordernumber' => '3', 'ordertime' => '2016-12-26', 'status' => 2],
                        ['ordernumber' => '4', 'ordertime' => '2016-12-27', 'status' => 2],
                    ]
                ]
            ]
        );
    }
}
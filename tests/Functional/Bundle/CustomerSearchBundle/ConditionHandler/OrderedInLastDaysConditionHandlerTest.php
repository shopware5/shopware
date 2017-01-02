<?php

namespace Shopware\Tests\Functional\Bundle\CustomerSearchBundle\ConditionHandler;


use Shopware\Bundle\CustomerSearchBundle\Condition\OrderedInLastDaysCondition;
use Shopware\Bundle\CustomerSearchBundle\Criteria;
use Shopware\Tests\Functional\Bundle\CustomerSearchBundle\TestCase;

class OrderedInLastDaysConditionHandlerTest extends TestCase
{
    public function testSingleDay()
    {
        $criteria = new Criteria();
        $criteria->addCondition(
            new OrderedInLastDaysCondition(5)
        );

        $this->search(
            $criteria,
            ['number1', 'number3'],
            [
                [
                    'email' => 'test1@example.com',
                    'number' => 'number1',
                    'orders' => [
                        [
                            'ordernumber' => '1',
                            'ordertime' => (new \DateTime())->format('Y-m-d'),
                            'status' => 2
                        ]
                    ]
                ],
                [
                    'email' => 'test2@example.com',
                    'number' => 'number2',
                    'orders' => [
                        [
                            'ordernumber' => '2',
                            'ordertime' => (new \DateTime())->sub(new \DateInterval('P10D'))->format('Y-m-d'),
                            'status' => 2
                        ]
                    ]
                ],
                [
                    'email' => 'test3@example.com',
                    'number' => 'number3',
                    'orders' => [
                        [
                            'ordernumber' => '3',
                            'ordertime' => (new \DateTime())->sub(new \DateInterval('P4D'))->format('Y-m-d'),
                            'status' => 2
                        ]
                    ]
                ]
            ]
        );
    }
}
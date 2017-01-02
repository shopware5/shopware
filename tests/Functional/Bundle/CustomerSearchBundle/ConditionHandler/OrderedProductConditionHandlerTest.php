<?php

namespace Shopware\Tests\Functional\Bundle\CustomerSearchBundle\ConditionHandler;



use Shopware\Bundle\CustomerSearchBundle\Condition\OrderedProductCondition;
use Shopware\Bundle\CustomerSearchBundle\Criteria;
use Shopware\Tests\Functional\Bundle\CustomerSearchBundle\TestCase;

class OrderedProductConditionHandlerTest extends TestCase
{
    public function testSingleProduct()
    {
        $criteria = new Criteria();
        $criteria->addCondition(
            new OrderedProductCondition(['SW100'])
        );

        $this->search(
            $criteria,
            ['number1'],
            [
                [
                    'email' => 'test1@example.com',
                    'number' => 'number1',
                    'orders' => [
                        [
                            'ordernumber' => '1',
                            'status' => 2,
                            'details' => [
                                ['ordernumber' => 'SW100', 'modus' => 0]
                            ]
                        ]
                    ]
                ],
                [
                    'email' => 'test2@example.com',
                    'number' => 'number2',
                    'orders' => [
                        [
                            'ordernumber' => '2',
                            'status' => 2,
                            'details' => [
                                ['ordernumber' => 'SW200', 'modus' => 0]
                            ]
                        ]
                    ]
                ],
                [
                    'email' => 'test3@example.com',
                    'number' => 'number3',
                    'orders' => [
                        [
                            'ordernumber' => '3',
                            'status' => 2,
                            'details' => [
                                ['ordernumber' => 'SW200', 'modus' => 0],
                                ['ordernumber' => 'SW100', 'modus' => 1]
                            ]
                        ]
                    ]
                ]
            ]
        );
    }


    public function testMultipleProducts()
    {
        $criteria = new Criteria();
        $criteria->addCondition(
            new OrderedProductCondition(['SW100', 'SW200'])
        );

        $this->search(
            $criteria,
            ['number1', 'number2'],
            [
                [
                    'email' => 'test1@example.com',
                    'number' => 'number1',
                    'orders' => [
                        [
                            'ordernumber' => '1',
                            'status' => 2,
                            'details' => [
                                ['ordernumber' => 'SW100', 'modus' => 0]
                            ]
                        ]
                    ]
                ],
                [
                    'email' => 'test2@example.com',
                    'number' => 'number2',
                    'orders' => [
                        [
                            'ordernumber' => '2',
                            'status' => 2,
                            'details' => [
                                ['ordernumber' => 'SW200', 'modus' => 0]
                            ]
                        ]
                    ]
                ],
                [
                    'email' => 'test3@example.com',
                    'number' => 'number3',
                    'orders' => [
                        [
                            'ordernumber' => '3',
                            'status' => 2,
                            'details' => [
                                ['ordernumber' => 'SW300', 'modus' => 0],
                                ['ordernumber' => 'SW100', 'modus' => 1]
                            ]
                        ]
                    ]
                ]
            ]
        );
    }
}
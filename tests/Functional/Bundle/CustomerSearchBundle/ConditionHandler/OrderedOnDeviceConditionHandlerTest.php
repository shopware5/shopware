<?php

namespace Shopware\Tests\Functional\Bundle\CustomerSearchBundle\ConditionHandler;


use Shopware\Bundle\CustomerSearchBundle\Condition\OrderedOnDeviceCondition;
use Shopware\Bundle\CustomerSearchBundle\Criteria;
use Shopware\Tests\Functional\Bundle\CustomerSearchBundle\TestCase;

class OrderedOnDeviceConditionHandlerTest extends TestCase
{
    public function testDesktopDevice()
    {
        $criteria = new Criteria();
        $criteria->addCondition(
            new OrderedOnDeviceCondition(['desktop'])
        );

        $this->search(
            $criteria,
            ['number1'],
            [
                [
                    'email' => 'test1@example.com',
                    'number' => 'number1',
                    'orders' => [
                        ['ordernumber' => '1', 'status' => 2, 'deviceType' => 'desktop']
                    ]
                ],
                [
                    'email' => 'test2@example.com',
                    'number' => 'number2',
                    'orders' => [
                        ['ordernumber' => '2', 'status' => 2, 'deviceType' => 'mobile']
                    ]
                ],
                [
                    'email' => 'test3@example.com',
                    'number' => 'number3',
                    'orders' => [
                        ['ordernumber' => '3', 'status' => 2]
                    ]
                ]
            ]
        );
    }

    public function testMobileAndDesktopDevice()
    {
        $criteria = new Criteria();
        $criteria->addCondition(
            new OrderedOnDeviceCondition(['desktop', 'mobile'])
        );

        $this->search(
            $criteria,
            ['number1', 'number2'],
            [
                [
                    'email' => 'test1@example.com',
                    'number' => 'number1',
                    'orders' => [
                        ['ordernumber' => '1', 'status' => 2, 'deviceType' => 'desktop']
                    ]
                ],
                [
                    'email' => 'test2@example.com',
                    'number' => 'number2',
                    'orders' => [
                        ['ordernumber' => '2', 'status' => 2, 'deviceType' => 'mobile']
                    ]
                ],
                [
                    'email' => 'test3@example.com',
                    'number' => 'number3',
                    'orders' => [
                        ['ordernumber' => '3', 'status' => 2],
                        ['ordernumber' => '4', 'status' => 2, 'deviceType' => 'tablet']
                    ]
                ]
            ]
        );
    }
}
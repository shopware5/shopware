<?php

namespace Shopware\Tests\Functional\Bundle\CustomerSearchBundle\ConditionHandler;

use Shopware\Bundle\CustomerSearchBundle\Condition\OrderedInShopCondition;
use Shopware\Bundle\CustomerSearchBundle\Criteria;
use Shopware\Tests\Functional\Bundle\CustomerSearchBundle\TestCase;

class OrderedInShopConditionHandlerTest extends TestCase
{
    public function testSingleShop()
    {
        $criteria = new Criteria();
        $criteria->addCondition(
            new OrderedInShopCondition([3])
        );

        $this->search(
            $criteria,
            ['number1'],
            [
                [
                    'email' => 'test1@example.com',
                    'number' => 'number1',
                    'orders' => [
                        ['ordernumber' => '1', 'status' => 2, 'subshopID' => 3]
                    ]
                ],
                [
                    'email' => 'test2@example.com',
                    'number' => 'number2',
                    'orders' => [
                        ['ordernumber' => '2', 'status' => 2, 'subshopID' => 2]
                    ]
                ]
            ]
        );
    }

    public function testMultipleShops()
    {
        $criteria = new Criteria();
        $criteria->addCondition(
            new OrderedInShopCondition([1, 3])
        );

        $this->search(
            $criteria,
            ['number1', 'number2'],
            [
                [
                    'email' => 'test1@example.com',
                    'number' => 'number1',
                    'orders' => [
                        ['ordernumber' => '1', 'status' => 2, 'subshopID' => 1]
                    ]
                ],
                [
                    'email' => 'test2@example.com',
                    'number' => 'number2',
                    'orders' => [
                        ['ordernumber' => '2', 'status' => 2, 'subshopID' => 1],
                        ['ordernumber' => '3', 'status' => 2, 'subshopID' => 3]
                    ]
                ],
                [
                    'email' => 'test3@example.com',
                    'number' => 'number3',
                    'orders' => [
                        ['ordernumber' => '4', 'status' => 2, 'subshopID' => 2]
                    ]
                ]
            ]
        );
    }
}
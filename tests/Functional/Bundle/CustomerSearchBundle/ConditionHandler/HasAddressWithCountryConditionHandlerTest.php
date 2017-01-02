<?php

namespace Shopware\Tests\Functional\Bundle\CustomerSearchBundle\ConditionHandler;

use Shopware\Bundle\CustomerSearchBundle\Condition\HasAddressWithCountryCondition;
use Shopware\Bundle\CustomerSearchBundle\Criteria;
use Shopware\Tests\Functional\Bundle\CustomerSearchBundle\TestCase;

class HasAddressWithCountryConditionHandlerTest extends TestCase
{
    public function testOneCountry()
    {
        $criteria = new Criteria();
        $criteria->addCondition(new HasAddressWithCountryCondition([2]));

        $this->search(
            $criteria,
            ['number1'],
            [
                [
                    'email' => 'test1@example.com',
                    'number' => 'number1',
                    'addresses' => [
                        ['country_id' => 2],
                        ['country_id' => 3]
                    ]
                ],
                [
                    'email' => 'test2@example.com',
                    'number' => 'number2',
                    'addresses' => [
                        ['country_id' => 4],
                        ['country_id' => 5]
                    ]
                ],
                [
                    'email' => 'test3@example.com',
                    'number' => 'number3'
                ]
            ]
        );
    }

    public function testTwoCountryIds()
    {
        $criteria = new Criteria();
        $criteria->addCondition(new HasAddressWithCountryCondition([3, 4]));

        $this->search(
            $criteria,
            ['number1', 'number2'],
            [
                [
                    'email' => 'test1@example.com',
                    'number' => 'number1',
                    'addresses' => [
                        ['country_id' => 2],
                        ['country_id' => 3]
                    ]
                ],
                [
                    'email' => 'test2@example.com',
                    'number' => 'number2',
                    'addresses' => [
                        ['country_id' => 4],
                        ['country_id' => 5]
                    ]
                ],
                [
                    'email' => 'test3@example.com',
                    'number' => 'number3',
                    'addresses' => [
                        ['country_id' => 5]
                    ]
                ]
            ]
        );
    }
}
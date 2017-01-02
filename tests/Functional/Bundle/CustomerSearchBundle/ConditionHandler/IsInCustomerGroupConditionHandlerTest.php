<?php

namespace Shopware\Tests\Functional\Bundle\CustomerSearchBundle\ConditionHandler;

use Shopware\Bundle\CustomerSearchBundle\Condition\IsInCustomerGroupCondition;
use Shopware\Bundle\CustomerSearchBundle\Criteria;
use Shopware\Tests\Functional\Bundle\CustomerSearchBundle\TestCase;

class IsInCustomerGroupConditionHandlerTest extends TestCase
{
    public function testSingleCustomerGroup()
    {
        $criteria = new Criteria();
        $criteria->addCondition(
            new IsInCustomerGroupCondition([1])
        );

        $this->search(
            $criteria,
            ['number1', 'number2'],
            [
                [
                    'email' => 'test1@example.com',
                    'number' => 'number1',
                    'customergroup' => 'EK',
                ],
                [
                    'email' => 'test2@example.com',
                    'number' => 'number2',
                    'customergroup' => 'EK',
                ],
                [
                    'email' => 'test3@example.com',
                    'number' => 'number3',
                    'customergroup' => 'H',
                ]
            ]
        );
    }

}
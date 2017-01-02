<?php

namespace Shopware\Tests\Functional\Bundle\CustomerSearchBundle\ConditionHandler;

use Shopware\Bundle\CustomerSearchBundle\Condition\IsCustomerSinceCondition;
use Shopware\Bundle\CustomerSearchBundle\Criteria;
use Shopware\Tests\Functional\Bundle\CustomerSearchBundle\TestCase;

class IsCustomerSinceConditionHandlerTest extends TestCase
{
    public function testDateTime()
    {
        $criteria = new Criteria();
        $criteria->addCondition(
            new IsCustomerSinceCondition(
                new \DateTime('2016-02-16')
            )
        );

        $this->search(
            $criteria,
            ['number2', 'number3'],
            [
                [
                    'email' => 'test1@example.com',
                    'number' => 'number1',
                    'firstlogin' => '2016-02-15',
                ],
                [
                    'email' => 'test2@example.com',
                    'number' => 'number2',
                    'firstlogin' => '2016-02-16',
                ],
                [
                    'email' => 'test3@example.com',
                    'number' => 'number3',
                    'firstlogin' => '2016-02-17',
                ]
            ]
        );
    }

    public function testWithStringDate()
    {
        $criteria = new Criteria();
        $criteria->addCondition(
            new IsCustomerSinceCondition('2016-02-16')
        );

        $this->search(
            $criteria,
            ['number2', 'number3'],
            [
                [
                    'email' => 'test1@example.com',
                    'number' => 'number1',
                    'firstlogin' => '2016-02-15',
                ],
                [
                    'email' => 'test2@example.com',
                    'number' => 'number2',
                    'firstlogin' => '2016-02-16',
                ],
                [
                    'email' => 'test3@example.com',
                    'number' => 'number3',
                    'firstlogin' => '2016-02-17',
                ]
            ]
        );
    }

}
<?php

namespace Shopware\Tests\Functional\Bundle\CustomerSearchBundle\ConditionHandler;

use Shopware\Bundle\CustomerSearchBundle\Condition\OrderedWithPaymentCondition;
use Shopware\Bundle\CustomerSearchBundle\Criteria;
use Shopware\Tests\Functional\Bundle\CustomerSearchBundle\TestCase;

class OrderedWithPaymentConditionHandlerTest extends TestCase
{
    /**
     * @var int
     */
    private $paymentId;

    protected function setUp()
    {
        parent::setUp();
        $this->connection->insert('s_core_paymentmeans', [
            'name' => 'unittest'
        ]);

        $this->paymentId = $this->connection->lastInsertId('s_core_paymentmeans');
    }

    public function testSinglePayment()
    {
        $criteria = new Criteria();
        $criteria->addCondition(
            new OrderedWithPaymentCondition([$this->paymentId])
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
                            'paymentID' => $this->paymentId
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
                            'paymentID' => -1
                        ]
                    ]
                ],
            ]
        );
    }
}
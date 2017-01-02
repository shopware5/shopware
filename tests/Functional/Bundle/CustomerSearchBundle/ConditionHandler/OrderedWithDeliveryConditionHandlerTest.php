<?php

namespace Shopware\Tests\Functional\Bundle\CustomerSearchBundle\ConditionHandler;

use Shopware\Bundle\CustomerSearchBundle\Condition\OrderedWithDeliveryCondition;
use Shopware\Bundle\CustomerSearchBundle\Criteria;
use Shopware\Tests\Functional\Bundle\CustomerSearchBundle\TestCase;

class OrderedWithDeliveryConditionHandlerTest extends TestCase
{

    /**
     * @var int
     */
    private $dispatchId;

    protected function setUp()
    {
        parent::setUp();
        $this->connection->insert('s_premium_dispatch', [
            'name' => 'unittest'
        ]);
        $this->dispatchId = $this->connection->lastInsertId('s_premium_dispatch');
    }

    public function testSingleDispatch()
    {
        $criteria = new Criteria();
        $criteria->addCondition(
            new OrderedWithDeliveryCondition([$this->dispatchId])
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
                            'dispatchID' => $this->dispatchId
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
                            'dispatchID' => -1
                        ]
                    ]
                ],
            ]
        );
    }
}
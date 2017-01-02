<?php

namespace Shopware\Tests\Functional\Bundle\CustomerSearchBundle\ConditionHandler;


use Shopware\Bundle\CustomerSearchBundle\Condition\OrderedProductOfManufacturerCondition;
use Shopware\Bundle\CustomerSearchBundle\Criteria;
use Shopware\Models\Article\Article;
use Shopware\Tests\Functional\Bundle\CustomerSearchBundle\TestCase;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\Helper;

class OrderedProductOfManufacturerConditionHandlerTest extends TestCase
{
    /**
     * @var int
     */
    private $manufacturerId;

    /**
     * @var Article
     */
    private $sw1;

    /**
     * @var Article
     */
    private $sw2;


    protected function setUp()
    {
        parent::setUp();

        $helper = new Helper();

        $manufacturer = $helper->createManufacturer($helper->getManufacturerData());

        $this->manufacturerId = $manufacturer->getId();

        $this->sw1 = $helper->createArticle(
            array_merge(
                $helper->getSimpleProduct('SW1'),
                ['supplierId' => $manufacturer->getId()]
            )
        );

        $this->sw2 = $helper->createArticle(
            array_merge(
                $helper->getSimpleProduct('SW2'),
                ['supplierId' => $manufacturer->getId()]
            )
        );
    }

    public function testSingleManufacturer()
    {
        $criteria = new Criteria();
        $criteria->addCondition(
            new OrderedProductOfManufacturerCondition([
                $this->manufacturerId
            ])
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
                                ['ordernumber' => 'SW1', 'modus' => 0, 'articleID' => $this->sw1->getId()]
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
                                ['ordernumber' => 'SW2', 'modus' => 0, 'articleID' => $this->sw2->getId()]
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
}
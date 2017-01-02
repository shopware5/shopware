<?php

namespace Shopware\Tests\Functional\Bundle\CustomerSearchBundle\ConditionHandler;

use Shopware\Bundle\CustomerSearchBundle\Condition\OrderedProductOfCategoryCondition;
use Shopware\Bundle\CustomerSearchBundle\Criteria;
use Shopware\Models\Article\Article;
use Shopware\Tests\Functional\Bundle\CustomerSearchBundle\TestCase;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\Helper;

class OrderedProductOfCategoryConditionHandlerTest extends TestCase
{
    /**
     * @var int
     */
    private $categoryId;

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
        $category = $helper->createCategory();
        $this->categoryId = $category->getId();

        $this->sw1 = $helper->createArticle(
            array_merge(
                $helper->getSimpleProduct('SW1'),
                ['categories' => [['id' => $category->getId()]]]
            )
        );

        $this->sw2 = $helper->createArticle(
            array_merge(
                $helper->getSimpleProduct('SW2'),
                ['categories' => [['id' => $category->getId()]]]
            )
        );
    }

    public function testSingleProduct()
    {
        $criteria = new Criteria();
        $criteria->addCondition(
            new OrderedProductOfCategoryCondition([
                $this->categoryId
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
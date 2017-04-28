<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Tests\Functional\Bundle\CustomerSearchBundleDBAL\ConditionHandler;

use Shopware\Bundle\CustomerSearchBundle\Condition\OrderedProductOfCategoryCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Models\Article\Article;
use Shopware\Tests\Functional\Bundle\CustomerSearchBundleDBAL\TestCase;
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
                $this->categoryId,
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
                                ['articleordernumber' => 'SW1', 'modus' => 0, 'articleID' => $this->sw1->getId()],
                            ],
                        ],
                    ],
                ],
                [
                    'email' => 'test2@example.com',
                    'number' => 'number2',
                    'orders' => [
                        [
                            'ordernumber' => '2',
                            'status' => 2,
                            'details' => [
                                ['articleordernumber' => 'SW2', 'modus' => 0, 'articleID' => $this->sw2->getId()],
                            ],
                        ],
                    ],
                ],
                [
                    'email' => 'test3@example.com',
                    'number' => 'number3',
                    'orders' => [
                        [
                            'ordernumber' => '3',
                            'status' => 2,
                            'details' => [
                                ['articleordernumber' => 'SW200', 'modus' => 0],
                                ['articleordernumber' => 'SW100', 'modus' => 1],
                            ],
                        ],
                    ],
                ],
            ]
        );
    }
}

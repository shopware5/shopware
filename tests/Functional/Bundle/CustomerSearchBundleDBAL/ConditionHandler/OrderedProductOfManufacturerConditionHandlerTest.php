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

use Shopware\Bundle\CustomerSearchBundle\Condition\OrderedProductOfManufacturerCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Models\Article\Article;
use Shopware\Tests\Functional\Bundle\CustomerSearchBundleDBAL\TestCase;
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

    protected function setUp(): void
    {
        parent::setUp();

        $helper = new Helper();

        $manufacturer = $helper->createManufacturer($helper->getManufacturerData());

        $this->manufacturerId = $manufacturer->getId();

        $this->sw1 = $helper->createArticle(
            array_merge(
                $helper->getSimpleProduct('SW1'),
                ['supplierId' => $manufacturer->getId(), 'categories' => [['id' => 3]]]
            )
        );

        $this->sw2 = $helper->createArticle(
            array_merge(
                $helper->getSimpleProduct('SW2'),
                ['supplierId' => $manufacturer->getId(), 'categories' => [['id' => 3]]]
            )
        );
    }

    public function testSingleManufacturer()
    {
        $criteria = new Criteria();
        $criteria->addCondition(
            new OrderedProductOfManufacturerCondition([
                $this->manufacturerId,
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
                                ['articleID' => $this->sw1->getId(), 'articleordernumber' => $this->sw1->getMainDetail()->getNumber(), 'modus' => 0],
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
                                ['articleID' => $this->sw2->getId(), 'articleordernumber' => $this->sw2->getMainDetail()->getNumber(), 'modus' => 0],
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
                                ['articleordernumber' => $this->sw2->getMainDetail()->getNumber(), 'modus' => 1, 'articleID' => $this->sw2->getId()],
                            ],
                        ],
                    ],
                ],
            ]
        );
    }
}

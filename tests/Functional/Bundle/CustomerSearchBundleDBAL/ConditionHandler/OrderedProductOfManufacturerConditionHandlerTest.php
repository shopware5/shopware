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
use Shopware\Models\Article\Article as ProductModel;
use Shopware\Models\Order\Status;
use Shopware\Tests\Functional\Bundle\CustomerSearchBundleDBAL\TestCase;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\Helper;

class OrderedProductOfManufacturerConditionHandlerTest extends TestCase
{
    /**
     * @var int
     */
    private $manufacturerId;

    /**
     * @var ProductModel
     */
    private $sw1;

    /**
     * @var ProductModel
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
                            'status' => Status::ORDER_STATE_COMPLETED,
                            'details' => [
                                ['articleID' => $this->sw1->getId(), 'articleordernumber' => $this->sw1->getMainDetail()->getNumber(), 'modus' => ProductModel::MODE_PRODUCT],
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
                            'status' => Status::ORDER_STATE_COMPLETED,
                            'details' => [
                                ['articleID' => $this->sw2->getId(), 'articleordernumber' => $this->sw2->getMainDetail()->getNumber(), 'modus' => ProductModel::MODE_PRODUCT],
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
                            'status' => Status::ORDER_STATE_COMPLETED,
                            'details' => [
                                ['articleordernumber' => $this->sw2->getMainDetail()->getNumber(), 'modus' => ProductModel::MODE_PREMIUM_PRODUCT, 'articleID' => $this->sw2->getId()],
                            ],
                        ],
                    ],
                ],
            ]
        );
    }
}

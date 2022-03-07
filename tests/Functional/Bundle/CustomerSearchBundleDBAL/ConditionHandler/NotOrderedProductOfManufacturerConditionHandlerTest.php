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

use Shopware\Bundle\CustomerSearchBundle\Condition\NotOrderedProductOfManufacturerCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Models\Article\Article;
use Shopware\Models\Article\Detail;
use Shopware\Tests\Functional\Bundle\CustomerSearchBundleDBAL\TestCase;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\Helper;
use Shopware\Tests\Functional\Traits\ContainerTrait;

class NotOrderedProductOfManufacturerConditionHandlerTest extends TestCase
{
    use ContainerTrait;

    private int $manufacturerId;

    private Article $sw1;

    private Article $sw2;

    protected function setUp(): void
    {
        parent::setUp();

        $helper = new Helper($this->getContainer());

        $manufacturer = $helper->createManufacturer($helper->getManufacturerData());

        $this->manufacturerId = $manufacturer->getId();

        $this->sw1 = $helper->createProduct(
            array_merge(
                $helper->getSimpleProduct('SW1'),
                ['supplierId' => $manufacturer->getId(), 'categories' => [['id' => 3]]]
            )
        );

        $this->sw2 = $helper->createProduct(
            array_merge(
                $helper->getSimpleProduct('SW2'),
                ['supplierId' => $manufacturer->getId(), 'categories' => [['id' => 3]]]
            )
        );
    }

    public function testSingleManufacturer(): void
    {
        $criteria = new Criteria();
        $criteria->addCondition(
            new NotOrderedProductOfManufacturerCondition([
                $this->manufacturerId,
            ])
        );

        $mainDetail = $this->sw1->getMainDetail();
        $mainDetail2 = $this->sw2->getMainDetail();

        static::assertInstanceOf(Detail::class, $mainDetail);
        static::assertInstanceOf(Detail::class, $mainDetail2);

        $this->search(
            $criteria,
            ['number3'],
            [
                [
                    'email' => 'test1@example.com',
                    'number' => 'number1',
                    'orders' => [
                        [
                            'ordernumber' => '1',
                            'status' => 2,
                            'details' => [
                                ['articleID' => $this->sw1->getId(), 'articleordernumber' => $mainDetail->getNumber(), 'modus' => 0],
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
                                ['articleID' => $this->sw2->getId(), 'articleordernumber' => $mainDetail2->getNumber(), 'modus' => 0],
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
                                ['articleordernumber' => $mainDetail2->getNumber(), 'modus' => 1, 'articleID' => $this->sw2->getId()],
                            ],
                        ],
                    ],
                ],
            ]
        );
    }
}

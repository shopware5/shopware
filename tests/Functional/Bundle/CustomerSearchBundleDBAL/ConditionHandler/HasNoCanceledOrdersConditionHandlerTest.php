<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Tests\Functional\Bundle\CustomerSearchBundleDBAL\ConditionHandler;

use Shopware\Bundle\CustomerSearchBundle\Condition\HasNoCanceledOrdersCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Tests\Functional\Bundle\CustomerSearchBundleDBAL\TestCase;

class HasNoCanceledOrdersConditionHandlerTest extends TestCase
{
    public function testWithOneCanceledOrder(): void
    {
        $criteria = new Criteria();
        $criteria->addCondition(new HasNoCanceledOrdersCondition());

        $this->search(
            $criteria,
            ['number2'],
            [
                [
                    'email' => 'test1@example.com',
                    'number' => 'number1',
                    'orders' => [
                        ['status' => -1, 'ordernumber' => '1'],
                    ],
                ],
                [
                    'email' => 'test2@example.com',
                    'number' => 'number2',
                    'orders' => [
                        ['status' => 2, 'ordernumber' => '2'],
                    ],
                ],
            ]
        );
    }

    public function testWithMultipleOrders(): void
    {
        $criteria = new Criteria();
        $criteria->addCondition(new HasNoCanceledOrdersCondition());

        $this->search(
            $criteria,
            ['number3'],
            [
                [
                    'email' => 'test1@example.com',
                    'number' => 'number1',
                    'orders' => [
                        ['status' => 2, 'ordernumber' => '1'],
                        ['status' => 12, 'ordernumber' => '2'],
                        ['status' => 4, 'ordernumber' => '3'],
                        ['status' => 0, 'ordernumber' => '4'],
                        ['status' => -1, 'ordernumber' => '10'],
                        ['status' => -1, 'ordernumber' => '5'],
                    ],
                ],
                [
                    'email' => 'test2@example.com',
                    'number' => 'number2',
                    'orders' => [
                        ['status' => -1, 'ordernumber' => '6'],
                    ],
                ],
                [
                    'email' => 'test3@example.com',
                    'number' => 'number3',
                    'orders' => [
                        ['status' => 3, 'ordernumber' => '6'],
                        ['status' => 4, 'ordernumber' => '7'],
                        ['status' => 5, 'ordernumber' => '8'],
                    ],
                ],
            ]
        );
    }
}

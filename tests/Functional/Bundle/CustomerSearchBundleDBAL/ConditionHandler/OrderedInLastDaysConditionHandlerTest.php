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

use DateInterval;
use DateTime;
use Shopware\Bundle\CustomerSearchBundle\Condition\OrderedInLastDaysCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Tests\Functional\Bundle\CustomerSearchBundleDBAL\TestCase;

class OrderedInLastDaysConditionHandlerTest extends TestCase
{
    public function testSingleDay()
    {
        $criteria = new Criteria();
        $criteria->addCondition(
            new OrderedInLastDaysCondition(5)
        );

        $this->search(
            $criteria,
            ['number1', 'number3'],
            [
                [
                    'email' => 'test1@example.com',
                    'number' => 'number1',
                    'orders' => [
                        [
                            'ordernumber' => '1',
                            'ordertime' => (new DateTime())->format('Y-m-d'),
                            'status' => 2,
                        ],
                    ],
                ],
                [
                    'email' => 'test2@example.com',
                    'number' => 'number2',
                    'orders' => [
                        [
                            'ordernumber' => '2',
                            'ordertime' => (new DateTime())->sub(new DateInterval('P10D'))->format('Y-m-d'),
                            'status' => 2,
                        ],
                    ],
                ],
                [
                    'email' => 'test3@example.com',
                    'number' => 'number3',
                    'orders' => [
                        [
                            'ordernumber' => '3',
                            'ordertime' => (new DateTime())->sub(new DateInterval('P4D'))->format('Y-m-d'),
                            'status' => 2,
                        ],
                    ],
                ],
            ]
        );
    }
}

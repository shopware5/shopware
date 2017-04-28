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

use Shopware\Bundle\CustomerSearchBundle\Condition\OrderedAtWeekdayCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Tests\Functional\Bundle\CustomerSearchBundleDBAL\TestCase;

class OrderedAtWeekdayConditionHandlerTest extends TestCase
{
    public function testSingleDay()
    {
        $criteria = new Criteria();
        $criteria->addCondition(
            new OrderedAtWeekdayCondition([
                'monday',
            ])
        );

        $this->search(
            $criteria,
            ['number1', 'number3'],
            [
                [
                    'email' => 'test1@example.com',
                    'number' => 'number1',
                    'orders' => [
                        ['ordernumber' => '1', 'ordertime' => '2016-12-26', 'status' => 2],
                    ],
                ],
                [
                    'email' => 'test2@example.com',
                    'number' => 'number2',
                    'orders' => [
                        ['ordernumber' => '2', 'ordertime' => '2016-12-13', 'status' => 2],
                    ],
                ],
                [
                    'email' => 'test3@example.com',
                    'number' => 'number3',
                    'orders' => [
                        ['ordernumber' => '3', 'ordertime' => '2016-12-27', 'status' => 2],
                        ['ordernumber' => '4', 'ordertime' => '2016-12-19', 'status' => 2],
                    ],
                ],
            ]
        );
    }

    public function testDifferentDays()
    {
        $criteria = new Criteria();
        $criteria->addCondition(
            new OrderedAtWeekdayCondition([
                'monday', 'tuesday',
            ])
        );

        $this->search(
            $criteria,
            ['number1', 'number3'],
            [
                [
                    'email' => 'test1@example.com',
                    'number' => 'number1',
                    'orders' => [
                        ['ordernumber' => '1', 'ordertime' => '2016-12-05', 'status' => 2],
                    ],
                ],
                [
                    'email' => 'test2@example.com',
                    'number' => 'number2',
                    'orders' => [
                        ['ordernumber' => '2', 'ordertime' => '2016-12-14', 'status' => 2],
                    ],
                ],
                [
                    'email' => 'test3@example.com',
                    'number' => 'number3',
                    'orders' => [
                        ['ordernumber' => '3', 'ordertime' => '2016-12-26', 'status' => 2],
                        ['ordernumber' => '4', 'ordertime' => '2016-12-27', 'status' => 2],
                    ],
                ],
            ]
        );
    }
}

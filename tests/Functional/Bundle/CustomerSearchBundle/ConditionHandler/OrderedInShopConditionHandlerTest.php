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

namespace Shopware\Tests\Functional\Bundle\CustomerSearchBundle\ConditionHandler;

use Shopware\Bundle\CustomerSearchBundle\Condition\OrderedInShopCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Tests\Functional\Bundle\CustomerSearchBundle\TestCase;

class OrderedInShopConditionHandlerTest extends TestCase
{
    public function testSingleShop()
    {
        $criteria = new Criteria();
        $criteria->addCondition(
            new OrderedInShopCondition([3])
        );

        $this->search(
            $criteria,
            ['number1'],
            [
                [
                    'email' => 'test1@example.com',
                    'number' => 'number1',
                    'orders' => [
                        ['ordernumber' => '1', 'status' => 2, 'subshopID' => 3],
                    ],
                ],
                [
                    'email' => 'test2@example.com',
                    'number' => 'number2',
                    'orders' => [
                        ['ordernumber' => '2', 'status' => 2, 'subshopID' => 2],
                    ],
                ],
            ]
        );
    }

    public function testMultipleShops()
    {
        $criteria = new Criteria();
        $criteria->addCondition(
            new OrderedInShopCondition([1, 3])
        );

        $this->search(
            $criteria,
            ['number1', 'number2'],
            [
                [
                    'email' => 'test1@example.com',
                    'number' => 'number1',
                    'orders' => [
                        ['ordernumber' => '1', 'status' => 2, 'subshopID' => 1],
                    ],
                ],
                [
                    'email' => 'test2@example.com',
                    'number' => 'number2',
                    'orders' => [
                        ['ordernumber' => '2', 'status' => 2, 'subshopID' => 1],
                        ['ordernumber' => '3', 'status' => 2, 'subshopID' => 3],
                    ],
                ],
                [
                    'email' => 'test3@example.com',
                    'number' => 'number3',
                    'orders' => [
                        ['ordernumber' => '4', 'status' => 2, 'subshopID' => 2],
                    ],
                ],
            ]
        );
    }
}

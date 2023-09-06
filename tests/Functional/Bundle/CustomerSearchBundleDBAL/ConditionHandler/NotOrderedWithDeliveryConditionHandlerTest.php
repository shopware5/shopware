<?php

declare(strict_types=1);
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

use Shopware\Bundle\CustomerSearchBundle\Condition\NotOrderedWithDeliveryCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Tests\Functional\Bundle\CustomerSearchBundleDBAL\TestCase;

class NotOrderedWithDeliveryConditionHandlerTest extends TestCase
{
    private int $dispatchId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->insert('s_premium_dispatch', [
            'name' => 'unittest',
        ]);
        $this->dispatchId = (int) $this->connection->lastInsertId('s_premium_dispatch');
    }

    public function testSingleDispatch(): void
    {
        $criteria = new Criteria();
        $criteria->addCondition(
            new NotOrderedWithDeliveryCondition([$this->dispatchId])
        );

        $this->search(
            $criteria,
            ['number2'],
            [
                [
                    'email' => 'test1@example.com',
                    'number' => 'number1',
                    'orders' => [
                        [
                            'ordernumber' => '1',
                            'status' => 2,
                            'dispatchID' => $this->dispatchId,
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
                            'dispatchID' => -1,
                        ],
                    ],
                ],
            ]
        );
    }
}

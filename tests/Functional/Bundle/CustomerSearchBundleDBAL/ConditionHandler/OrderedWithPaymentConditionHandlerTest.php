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

use Shopware\Bundle\CustomerSearchBundle\Condition\OrderedWithPaymentCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Tests\Functional\Bundle\CustomerSearchBundleDBAL\TestCase;

class OrderedWithPaymentConditionHandlerTest extends TestCase
{
    private int $paymentId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->insert('s_core_paymentmeans', [
            'name' => 'unittest',
        ]);

        $this->paymentId = (int) $this->connection->lastInsertId('s_core_paymentmeans');
    }

    public function testSinglePayment(): void
    {
        $criteria = new Criteria();
        $criteria->addCondition(
            new OrderedWithPaymentCondition([$this->paymentId])
        );

        $this->search(
            $criteria,
            ['number1'],
            [
                [
                    'email' => 'test1@example.com',
                    'number' => 'number1',
                    'orders' => [
                        [
                            'ordernumber' => '1',
                            'status' => 2,
                            'paymentID' => $this->paymentId,
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
                            'paymentID' => -1,
                        ],
                    ],
                ],
            ]
        );
    }
}

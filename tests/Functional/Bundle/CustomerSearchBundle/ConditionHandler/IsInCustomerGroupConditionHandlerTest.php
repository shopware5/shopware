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

use Shopware\Bundle\CustomerSearchBundle\Condition\IsInCustomerGroupCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Tests\Functional\Bundle\CustomerSearchBundle\TestCase;

class IsInCustomerGroupConditionHandlerTest extends TestCase
{
    public function testSingleCustomerGroup()
    {
        $criteria = new Criteria();
        $criteria->addCondition(
            new IsInCustomerGroupCondition([1])
        );

        $this->search(
            $criteria,
            ['number1', 'number2'],
            [
                [
                    'email' => 'test3@example.com',
                    'number' => 'number3',
                    'customergroup' => 'H',
                ],
                [
                    'email' => 'test1@example.com',
                    'number' => 'number1',
                    'customergroup' => 'EK',
                ],
                [
                    'email' => 'test2@example.com',
                    'number' => 'number2',
                    'customergroup' => 'EK',
                ],
            ]
        );
    }
}

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

use Shopware\Bundle\CustomerSearchBundle\Condition\SalutationCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Tests\Functional\Bundle\CustomerSearchBundleDBAL\TestCase;

class SalutationConditionHandlerTest extends TestCase
{
    public function testMaleCustomer()
    {
        $criteria = new Criteria();
        $criteria->addCondition(new SalutationCondition(['mr']));

        $this->search(
            $criteria,
            ['number1', 'number4'],
            [
                [
                    'email' => 'test1@example.com',
                    'number' => 'number1',
                    'salutation' => 'mr',
                ],
                [
                    'email' => 'test2@example.com',
                    'number' => 'number2',
                    'salutation' => 'mrs',
                ],
                [
                    'email' => 'test3@example.com',
                    'number' => 'number3',
                ],
                [
                    'email' => 'test4@example.com',
                    'number' => 'number4',
                    'salutation' => 'mr',
                ],
                [
                    'email' => 'test5@example.com',
                    'number' => 'number5',
                    'salutation' => null,
                ],
            ]
        );
    }

    public function testFemaleCustomer()
    {
        $criteria = new Criteria();
        $criteria->addCondition(new SalutationCondition(['mrs']));

        $this->search(
            $criteria,
            ['number1', 'number2'],
            [
                [
                    'email' => 'test1@example.com',
                    'number' => 'number1',
                    'salutation' => 'mrs',
                ],
                [
                    'email' => 'test2@example.com',
                    'number' => 'number2',
                    'salutation' => 'mrs',
                ],
                [
                    'email' => 'test3@example.com',
                    'number' => 'number3',
                ],
                [
                    'email' => 'test4@example.com',
                    'number' => 'number4',
                    'salutation' => 'mr',
                ],
                [
                    'email' => 'test5@example.com',
                    'number' => 'number5',
                    'salutation' => null,
                ],
            ]
        );
    }

    public function testMissingSalutation()
    {
        $criteria = new Criteria();
        $criteria->addCondition(new SalutationCondition(['mr']));

        $result = $this->search(
            $criteria,
            [],
            [
                [
                    'email' => 'test1@example.com',
                    'number' => 'number1',
                    'salutation' => null,
                ],
                [
                    'email' => 'test2@example.com',
                    'number' => 'number2',
                ],
            ]
        );

        $this->assertEquals(0, $result->getTotal());
    }

    public function testMultipleSalutations()
    {
        $criteria = new Criteria();
        $criteria->addCondition(new SalutationCondition(['mrs', 'mr']));

        $this->search(
            $criteria,
            ['number1', 'number2', 'number4'],
            [
                [
                    'email' => 'test1@example.com',
                    'number' => 'number1',
                    'salutation' => 'mrs',
                ],
                [
                    'email' => 'test2@example.com',
                    'number' => 'number2',
                    'salutation' => 'mrs',
                ],
                [
                    'email' => 'test3@example.com',
                    'number' => 'number3',
                ],
                [
                    'email' => 'test4@example.com',
                    'number' => 'number4',
                    'salutation' => 'mr',
                ],
                [
                    'email' => 'test5@example.com',
                    'number' => 'number5',
                    'salutation' => null,
                ],
            ]
        );
    }
}

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

use Shopware\Bundle\CustomerSearchBundle\Condition\IsCustomerSinceCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Tests\Functional\Bundle\CustomerSearchBundleDBAL\TestCase;

class IsCustomerSinceConditionHandlerTest extends TestCase
{
    public function testDateTime()
    {
        $criteria = new Criteria();
        $criteria->addCondition(
            new IsCustomerSinceCondition(
                new \DateTime('2016-02-16')
            )
        );

        $this->search(
            $criteria,
            ['number2', 'number3'],
            [
                [
                    'email' => 'test1@example.com',
                    'number' => 'number1',
                    'firstlogin' => '2016-02-15',
                ],
                [
                    'email' => 'test2@example.com',
                    'number' => 'number2',
                    'firstlogin' => '2016-02-16',
                ],
                [
                    'email' => 'test3@example.com',
                    'number' => 'number3',
                    'firstlogin' => '2016-02-17',
                ],
            ]
        );
    }

    public function testWithStringDate()
    {
        $criteria = new Criteria();
        $criteria->addCondition(
            new IsCustomerSinceCondition('2016-02-16')
        );

        $this->search(
            $criteria,
            ['number2', 'number3'],
            [
                [
                    'email' => 'test1@example.com',
                    'number' => 'number1',
                    'firstlogin' => '2016-02-15',
                ],
                [
                    'email' => 'test2@example.com',
                    'number' => 'number2',
                    'firstlogin' => '2016-02-16',
                ],
                [
                    'email' => 'test3@example.com',
                    'number' => 'number3',
                    'firstlogin' => '2016-02-17',
                ],
            ]
        );
    }
}

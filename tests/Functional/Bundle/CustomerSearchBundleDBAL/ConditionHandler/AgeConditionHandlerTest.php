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

use Shopware\Bundle\CustomerSearchBundle\Condition\AgeCondition;
use Shopware\Bundle\SearchBundle\ConditionInterface;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Tests\Functional\Bundle\CustomerSearchBundleDBAL\TestCase;

class AgeConditionHandlerTest extends TestCase
{
    public function testEquals()
    {
        $criteria = new Criteria();
        $criteria->addCondition(
            new AgeCondition(ConditionInterface::OPERATOR_EQ, 25)
        );

        $date1 = new \DateTime();
        $date1->sub(new \DateInterval('P25Y'));

        $date2 = new \DateTime();
        $date2->sub(new \DateInterval('P24Y'));

        $this->search(
            $criteria,
            ['number2'],
            [
                [
                    'email' => 'test1@example.com',
                    'number' => 'number1',
                    'birthday' => '1990-10-10',
                ],
                [
                    'email' => 'test2@example.com',
                    'number' => 'number2',
                    'birthday' => $date1->format('Y-m-d'),
                ],
                [
                    'email' => 'test3@example.com',
                    'number' => 'number3',
                    'birthday' => $date2->format('Y-m-d'),
                ],
                [
                    'email' => 'test4@example.com',
                    'number' => 'number4',
                ],
            ]
        );
    }

    public function testLower()
    {
        $criteria = new Criteria();
        $criteria->addCondition(
            new AgeCondition(ConditionInterface::OPERATOR_LT, 25)
        );

        $date = new \DateTime();
        $date->sub(new \DateInterval('P24Y'));

        $this->search(
            $criteria,
            ['number2'],
            [
                [
                    'email' => 'test1@example.com',
                    'number' => 'number1',
                    'birthday' => '1990-10-10',
                ],
                [
                    'email' => 'test2@example.com',
                    'number' => 'number2',
                    'birthday' => $date->format('Y-m-d'),
                ],
                [
                    'email' => 'test3@example.com',
                    'number' => 'number3',
                ],
            ]
        );
    }

    public function testLowerEquals()
    {
        $criteria = new Criteria();
        $criteria->addCondition(
            new AgeCondition(ConditionInterface::OPERATOR_LTE, 25)
        );

        $date1 = new \DateTime();
        $date1->sub(new \DateInterval('P24Y'));

        $date2 = new \DateTime();
        $date2->sub(new \DateInterval('P25Y'));

        $this->search(
            $criteria,
            ['number2', 'number3'],
            [
                [
                    'email' => 'test1@example.com',
                    'number' => 'number1',
                    'birthday' => '1990-10-10',
                ],
                [
                    'email' => 'test2@example.com',
                    'number' => 'number2',
                    'birthday' => $date1->format('Y-m-d'),
                ],
                [
                    'email' => 'test3@example.com',
                    'number' => 'number3',
                    'birthday' => $date2->format('Y-m-d'),
                ],
                [
                    'email' => 'test4@example.com',
                    'number' => 'number4',
                ],
            ]
        );
    }

    public function testBetween()
    {
        $criteria = new Criteria();
        $criteria->addCondition(
            new AgeCondition(ConditionInterface::OPERATOR_BETWEEN,
                ['min' => 25, 'max' => 35])
        );

        $date1 = new \DateTime();
        $date1->sub(new \DateInterval('P25Y'));

        $date2 = new \DateTime();
        $date2->sub(new \DateInterval('P30Y'));

        $date3 = new \DateTime();
        $date3->sub(new \DateInterval('P35Y'));

        $date4 = new \DateTime();
        $date4->sub(new \DateInterval('P24Y'));

        $this->search(
            $criteria,
            ['number2', 'number3', 'number4'],
            [
                [
                    'email' => 'test1@example.com',
                    'number' => 'number1',
                    'birthday' => '1980-10-10',
                ],
                [
                    'email' => 'test2@example.com',
                    'number' => 'number2',
                    'birthday' => $date1->format('Y-m-d'),
                ],
                [
                    'email' => 'test3@example.com',
                    'number' => 'number3',
                    'birthday' => $date2->format('Y-m-d'),
                ],
                [
                    'email' => 'test4@example.com',
                    'number' => 'number4',
                    'birthday' => $date3->format('Y-m-d'),
                ],
                [
                    'email' => 'test5@example.com',
                    'number' => 'number5',
                    'birthday' => $date4->format('Y-m-d'),
                ],
                [
                    'email' => 'test6@example.com',
                    'number' => 'number6',
                ],
            ]
        );
    }

    public function testGreater()
    {
        $criteria = new Criteria();
        $criteria->addCondition(
            new AgeCondition(ConditionInterface::OPERATOR_GT, 25)
        );

        $date1 = new \DateTime();
        $date1->sub(new \DateInterval('P25Y'));

        $date2 = new \DateTime();
        $date2->sub(new \DateInterval('P24Y'));

        $this->search(
            $criteria,
            ['number1'],
            [
                [
                    'email' => 'test1@example.com',
                    'number' => 'number1',
                    'birthday' => '1990-10-10',
                ],
                [
                    'email' => 'test2@example.com',
                    'number' => 'number2',
                    'birthday' => $date1->format('Y-m-d'),
                ],
                [
                    'email' => 'test3@example.com',
                    'number' => 'number3',
                    'birthday' => $date2->format('Y-m-d'),
                ],
                [
                    'email' => 'test4@example.com',
                    'number' => 'number4',
                ],
            ]
        );
    }

    public function testGreaterThanEquals()
    {
        $criteria = new Criteria();
        $criteria->addCondition(
            new AgeCondition(ConditionInterface::OPERATOR_GTE, 25)
        );

        $date = new \DateTime();
        $date->sub(new \DateInterval('P25Y'));

        $this->search(
            $criteria,
            ['number1', 'number2'],
            [
                [
                    'email' => 'test1@example.com',
                    'number' => 'number1',
                    'birthday' => '1990-10-10',
                ],
                [
                    'email' => 'test2@example.com',
                    'number' => 'number2',
                    'birthday' => $date->format('Y-m-d'),
                ],
                [
                    'email' => 'test3@example.com',
                    'number' => 'number3',
                ],
            ]
        );
    }
}

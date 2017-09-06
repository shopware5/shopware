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

use Shopware\Bundle\SearchBundle\Condition\SearchTermCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Tests\Functional\Bundle\CustomerSearchBundleDBAL\TestCase;

class SearchTermConditionHandlerTest extends TestCase
{
    public function testEmailSearch()
    {
        $criteria = new Criteria();
        $criteria->addCondition(
            new SearchTermCondition('test1')
        );

        $this->search(
            $criteria,
            ['number1'],
            [
                [
                    'email' => 'test1@example.com',
                    'number' => 'number1',
                ],
                [
                    'email' => 'test2@example.com',
                    'number' => 'number2',
                ],
                [
                    'email' => 'test3@example.com',
                    'number' => 'number3',
                ],
            ]
        );
    }

    public function testBirthdaySearch()
    {
        $criteria = new Criteria();
        $criteria->addCondition(
            new SearchTermCondition('1990')
        );

        $this->search(
            $criteria,
            ['number1'],
            [
                [
                    'email' => 'test1@example.com',
                    'number' => 'number1',
                    'birthday' => '1990-07-26',
                ],
                [
                    'email' => 'test2@example.com',
                    'number' => 'number2',
                    'birthday' => '1991-07-26',
                ],
                [
                    'email' => 'test3@example.com',
                    'number' => 'number3',
                ],
            ]
        );
    }

    public function testCustomerNumberSearch()
    {
        $criteria = new Criteria();
        $criteria->addCondition(
            new SearchTermCondition('number1')
        );

        $this->search(
            $criteria,
            ['number1'],
            [
                [
                    'email' => 'test1@example.com',
                    'number' => 'number1',
                ],
                [
                    'email' => 'test2@example.com',
                    'number' => 'number2',
                ],
                [
                    'email' => 'test3@example.com',
                    'number' => 'number3',
                ],
            ]
        );
    }

    public function testCompanySearch()
    {
        $criteria = new Criteria();
        $criteria->addCondition(
            new SearchTermCondition('shopware')
        );

        $this->search(
            $criteria,
            ['number1'],
            [
                [
                    'email' => 'test1@example.com',
                    'number' => 'number1',
                    'addresses' => [
                        ['company' => 'shopware', 'country_id' => 2],
                    ],
                ],
                [
                    'email' => 'test2@example.com',
                    'number' => 'number2',
                    'addresses' => [
                        ['company' => 'Google Inc.', 'country_id' => 2],
                    ],
                ],
                [
                    'email' => 'test3@example.com',
                    'number' => 'number3',
                ],
            ]
        );
    }

    public function testOrderedProductsSearch()
    {
        $criteria = new Criteria();
        $criteria->addCondition(
            new SearchTermCondition('SW10239')
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
                            'details' => [
                                ['articleID' => 272, 'articleordernumber' => 'SW10239', 'modus' => 0],
                            ],
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
                            'details' => [
                                ['articleID' => 246, 'articleordernumber' => 'SW10237', 'modus' => 0],
                            ],
                        ],
                    ],
                ],
                [
                    'email' => 'test3@example.com',
                    'number' => 'number3',
                    'orders' => [
                        [
                            'ordernumber' => '3',
                            'status' => 2,
                        ],
                    ],
                ],
            ]
        );
    }

    public function testTwoColumnSearch()
    {
        $criteria = new Criteria();
        $criteria->addCondition(
            new SearchTermCondition('PHP Cody')
        );

        $this->search(
            $criteria,
            ['number1', 'number2'],
            [
                [
                    'email' => 'test1@example.com',
                    'number' => 'number1',
                    'firstname' => 'Cody',
                    'lastname' => 'PHP',
                ],
                [
                    'email' => 'test2@example.com',
                    'number' => 'number2',
                    'lastname' => 'Cody',
                    'addresses' => [
                        ['company' => 'PHP', 'country_id' => 2],
                    ],
                ],
                [
                    'email' => 'test3@example.com',
                    'number' => 'number3',
                    'lastname' => 'PHP',
                ],
                [
                    'email' => 'test4@example.com',
                    'number' => 'number4',
                    'lastname' => 'Cody',
                ],
                [
                    'email' => 'test5@example.com',
                    'number' => 'number5',
                ],
            ]
        );
    }
}

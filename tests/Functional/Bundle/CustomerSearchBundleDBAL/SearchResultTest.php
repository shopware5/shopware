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

namespace Shopware\Tests\Functional\Bundle\CustomerSearchBundleDBAL;

use Shopware\Bundle\SearchBundle\Criteria;

class SearchResultTest extends TestCase
{
    public function testResultCollection()
    {
        $criteria = new Criteria();
        $result = $this->search(
            $criteria,
            ['number1', 'number2', 'number3'],
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

        static::assertContains('test1@example.com', $result->getEmails());
        static::assertContains('test2@example.com', $result->getEmails());
        static::assertContains('test3@example.com', $result->getEmails());
        static::assertEquals(3, $result->getTotal());
        static::assertCount(3, $result->getIds());
        static::assertCount(3, $result->getCustomers());
    }
}

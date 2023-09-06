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

namespace Shopware\Tests\Unit\Bundle\SearchBundle;

use DateTime;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\SearchBundle\Criteria;

class CriteriaTest extends TestCase
{
    /**
     * @dataProvider invalidCriteriaLimit
     *
     * @param int $limit
     */
    public function testInvalidCriteriaLimit($limit): void
    {
        $this->expectException('InvalidArgumentException');
        $criteria = new Criteria();
        $criteria->limit($limit);
    }

    /**
     * @dataProvider validCriteriaLimit
     *
     * @param int $limit
     */
    public function testValidCriteriaLimit($limit): void
    {
        $criteria = new Criteria();
        $criteria->limit($limit);
        static::assertEquals($criteria->getLimit(), $limit);
    }

    /**
     * @dataProvider invalidCriteriaOffset
     *
     * @param int $offset
     */
    public function testInvalidCriteriaOffset($offset): void
    {
        $this->expectException('InvalidArgumentException');
        $criteria = new Criteria();
        $criteria->offset($offset);
    }

    /**
     * @dataProvider validCriteriaOffset
     *
     * @param int $offset
     */
    public function testValidCriteriaOffset($offset): void
    {
        $criteria = new Criteria();
        $criteria->offset($offset);
        static::assertEquals($offset, $criteria->getOffset());
    }

    public function validCriteriaLimit(): array
    {
        return [
            [1],
            [null],
            [200],
        ];
    }

    public function validCriteriaOffset(): array
    {
        return [
            [0],
            [1],
            [20],
        ];
    }

    public function invalidCriteriaOffset(): array
    {
        return [
            [-1],
            ['123-2'],
            ['asfkln'],
            [null],
            [new DateTime()],
        ];
    }

    public function invalidCriteriaLimit(): array
    {
        return [
            [0],
            [-1],
            ['123-2'],
            ['asfkln'],
            [new DateTime()],
        ];
    }
}

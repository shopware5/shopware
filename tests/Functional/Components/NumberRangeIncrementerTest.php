<?php

declare(strict_types=1);
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

namespace Shopware\Tests\Functional\Components;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Shopware\Components\NumberRangeIncrementer;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class NumberRangeIncrementerTest extends TestCase
{
    use ContainerTrait;
    use DatabaseTransactionBehaviour;

    private Connection $connection;

    public function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->getContainer()->get(Connection::class);
    }

    public function testIncrement(): void
    {
        // Fetch actual number from DB
        $rangeName = 'invoice';
        $expectedNumber = $this->connection->fetchOne(
            'SELECT number
             FROM s_order_number
             WHERE name = ?',
            [$rangeName]
        );
        ++$expectedNumber;

        $manager = new NumberRangeIncrementer($this->connection);

        static::assertEquals($expectedNumber, $manager->increment($rangeName));
    }

    public function testIncrementWithInvalidName(): void
    {
        $manager = new NumberRangeIncrementer($this->connection);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Number range with name "invalid" does not exist.');
        $manager->increment('invalid');
    }
}

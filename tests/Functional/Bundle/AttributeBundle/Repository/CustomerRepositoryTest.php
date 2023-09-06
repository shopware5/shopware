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

namespace Shopware\Tests\Functional\Bundle\AttributeBundle\Repository;

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\AttributeBundle\Repository\CustomerRepository;
use Shopware\Tests\Functional\Traits\ContainerTrait;

class CustomerRepositoryTest extends TestCase
{
    use ContainerTrait;

    public function testGetMapping(): void
    {
        $mapping = $this->getCustomerRepository()->getMapping();

        $expectedDateTimeFormat = 'yyyy-MM-dd HH:mm:ss';
        $expectedDateFormat = 'yyyy-MM-dd';

        static::assertSame($expectedDateTimeFormat, $mapping['properties']['lastLogin']['format']);
        static::assertSame($expectedDateFormat, $mapping['properties']['birthday']['format']);
        static::assertSame($expectedDateTimeFormat, $mapping['properties']['firstLogin']['format']);
        static::assertSame($expectedDateTimeFormat, $mapping['properties']['lockedUntil']['format']);
    }

    private function getCustomerRepository(): CustomerRepository
    {
        return $this->getContainer()->get(CustomerRepository::class);
    }
}

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

namespace Shopware\Tests\Functional\Bundle\AttributeBundle\Repository\Reader;

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\AttributeBundle\Repository\Reader\OrderReader;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class OrderReaderTest extends TestCase
{
    use ContainerTrait;
    use DatabaseTransactionBehaviour;

    public function testGetList(): void
    {
        $sql = file_get_contents(__DIR__ . '/_fixtures/order.sql');
        static::assertIsString($sql);
        $this->getContainer()->get('dbal_connection')->executeStatement($sql);

        $orderReader = $this->getContainer()->get(OrderReader::class);
        $result = $orderReader->getList([390059, 390061, 390063]);

        $expectedResult = [
            390059 => ['SHIPPINGDISCOUNT', 'SW10173', 'SW10211', 'SW10229'],
            390061 => ['SHIPPINGDISCOUNT', 'SW10178', 'SW10179.1'],
            390063 => ['SHIPPINGDISCOUNT', 'SW10165', 'SW10169', 'SW10170'],
        ];

        foreach ($expectedResult as $oderId => $expectedResultArray) {
            foreach ($expectedResultArray as $expectedInArray) {
                static::assertContains($expectedInArray, $result[$oderId]['articleNumber']);
            }
        }
    }
}

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

namespace Shopware\Tests\Functional\Models\Order;

use Shopware\Models\Order\Shipping;

class ShippingTest extends \Enlight_Components_Test_TestCase
{
    public function testAddressFieldsLength(): void
    {
        $shipping = $this->getRandomShipping();

        $shippingId = $shipping->getId();
        $originalStreet = $shipping->getStreet();
        $originalZipCode = $shipping->getZipCode();

        $shipping->setStreet('This is a really really really long city name');
        $shipping->setZipCode('This is a really really really long zip code');

        Shopware()->Models()->persist($shipping);
        Shopware()->Models()->flush($shipping);
        Shopware()->Models()->clear();

        $shipping = Shopware()->Models()->getRepository(Shipping::class)->find($shippingId);
        static::assertEquals('This is a really really really long city name', $shipping->getStreet());
        static::assertEquals('This is a really really really long zip code', $shipping->getZipCode());

        $shipping->setStreet($originalStreet);
        $shipping->setZipCode($originalZipCode);

        Shopware()->Models()->persist($shipping);
        Shopware()->Models()->flush($shipping);
    }

    private function getRandomShipping(): Shipping
    {
        $ids = Shopware()->Models()->getRepository(Shipping::class)
            ->createQueryBuilder('b')
            ->select('b.id')
            ->getQuery()
            ->getArrayResult();

        shuffle($ids);

        $shipping = Shopware()->Models()->getRepository(Shipping::class)->find(array_shift($ids));
        static::assertInstanceOf(Shipping::class, $shipping);

        return $shipping;
    }
}

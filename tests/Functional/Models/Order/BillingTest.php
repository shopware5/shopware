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

namespace Shopware\Tests\Functional\Models\Order;

use Enlight_Components_Test_TestCase;
use Shopware\Models\Order\Billing;

class BillingTest extends Enlight_Components_Test_TestCase
{
    public function testAddressFieldsLength(): void
    {
        $billing = $this->getRandomBilling();

        $billingId = $billing->getId();
        $originalStreet = $billing->getStreet();
        $originalZipCode = $billing->getZipCode();

        $billing->setStreet('This is a really really really long city name');
        $billing->setZipCode('This is a really really really long zip code');

        Shopware()->Models()->persist($billing);
        Shopware()->Models()->flush($billing);
        Shopware()->Models()->clear();

        $billing = Shopware()->Models()->getRepository(Billing::class)->find($billingId);
        static::assertEquals('This is a really really really long city name', $billing->getStreet());
        static::assertEquals('This is a really really really long zip code', $billing->getZipCode());

        $billing->setStreet($originalStreet);
        $billing->setZipCode($originalZipCode);

        Shopware()->Models()->persist($billing);
        Shopware()->Models()->flush($billing);
    }

    private function getRandomBilling(): Billing
    {
        $ids = Shopware()->Models()->getRepository(Billing::class)
            ->createQueryBuilder('b')
            ->select('b.id')
            ->getQuery()
            ->getArrayResult();

        shuffle($ids);

        $billing = Shopware()->Models()->getRepository(Billing::class)->find(array_shift($ids));
        static::assertInstanceOf(Billing::class, $billing);

        return $billing;
    }
}

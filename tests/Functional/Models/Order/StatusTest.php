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

class StatusTest extends Enlight_Components_Test_TestCase
{
    public function testStatusIsPersistableViaOrm(): void
    {
        $order = new \Shopware\Models\Order\Status();
        $order->setId(123456789);
        $order->setPosition(123456789);
        $order->setSendMail(0);
        $order->setName('prettyIgnorableNameForThisTest');
        $order->setGroup(\Shopware\Models\Order\Status::GROUP_PAYMENT);

        Shopware()->Models()->persist($order);
        Shopware()->Models()->flush($order);

        // cleanup
        Shopware()->Models()->remove($order);
        Shopware()->Models()->flush($order);
        Shopware()->Models()->clear();

        static::assertNull(Shopware()->Models()->find(\Shopware\Models\Order\Status::class, $order->getId()));
    }
}

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

namespace Shopware\Tests\Functional\Components\Cart;

use PHPUnit\Framework\TestCase;
use Shopware\Components\Cart\CartOrderNumberProviderInterface;
use Shopware\Tests\Functional\Helper\Utils;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;
use Shopware_Components_Config as Config;

class CartOrderNumberProviderTest extends TestCase
{
    use DatabaseTransactionBehaviour;

    public function testGetIsSameAsConfig(): void
    {
        static::assertSame(
            $this->getConfig()->get(CartOrderNumberProviderInterface::DISCOUNT),
            $this->getCartProviderService()->get(CartOrderNumberProviderInterface::DISCOUNT)
        );

        static::assertSame(
            $this->getConfig()->get(CartOrderNumberProviderInterface::PAYMENT_ABSOLUTE),
            $this->getCartProviderService()->get(CartOrderNumberProviderInterface::PAYMENT_ABSOLUTE)
        );

        static::assertSame(
            $this->getConfig()->get(CartOrderNumberProviderInterface::PAYMENT_PERCENT),
            $this->getCartProviderService()->get(CartOrderNumberProviderInterface::PAYMENT_PERCENT)
        );

        static::assertSame(
            $this->getConfig()->get(CartOrderNumberProviderInterface::SURCHARGE),
            $this->getCartProviderService()->get(CartOrderNumberProviderInterface::SURCHARGE)
        );

        static::assertSame(
            $this->getConfig()->get(CartOrderNumberProviderInterface::SHIPPING_DISCOUNT),
            $this->getCartProviderService()->get(CartOrderNumberProviderInterface::SHIPPING_DISCOUNT)
        );

        static::assertSame(
            $this->getConfig()->get(CartOrderNumberProviderInterface::SHIPPING_SURCHARGE),
            $this->getCartProviderService()->get(CartOrderNumberProviderInterface::SHIPPING_SURCHARGE)
        );
    }

    public function testGetAll(): void
    {
        Shopware()->Container()->get('config_writer')->save(CartOrderNumberProviderInterface::DISCOUNT, 'test', null, 2);
        $config = $this->getCartProviderService()->getAll(CartOrderNumberProviderInterface::DISCOUNT);

        static::assertContains('test', $config);
    }

    private function getCartProviderService(): CartOrderNumberProviderInterface
    {
        $service = Shopware()->Container()->get(CartOrderNumberProviderInterface::class);
        Utils::hijackProperty($service, 'data', null);

        return $service;
    }

    private function getConfig(): Config
    {
        return Shopware()->Container()->get('config');
    }
}

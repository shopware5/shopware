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

namespace Shopware\Tests\Functional\Components\Cart;

use Shopware\Components\Cart\PaymentTokenService;
use Shopware\Components\Cart\Struct\PaymentTokenResult;

class PaymentTokenServiceTest extends \Enlight_Components_Test_Controller_TestCase
{
    /**
     * @var PaymentTokenService
     */
    private $service;

    public function setUp(): void
    {
        Shopware()->Container()->get('dbal_connection')->beginTransaction();
        $this->service = Shopware()->Container()->get('shopware.components.cart.payment_token');
    }

    protected function tearDown(): void
    {
        Shopware()->Container()->get('dbal_connection')->rollBack();
    }

    public function testPaymentTokenStorage(): void
    {
        $hash = $this->service->generate();

        $this->assertInstanceOf(PaymentTokenResult::class, $this->service->restore($hash));
        $this->assertNull($this->service->restore($hash));
    }

    public function testTokenNotExist(): void
    {
        $hash = 'i dont exist';

        $this->assertNull($this->service->restore($hash));
    }

    public function testPaymentTokenValidRequest(): void
    {
        $hash = $this->service->generate();

        $this->dispatch('/?swPaymentToken=' . $hash);
        $cookies = $this->Response()->getCookies();

        $this->assertArrayHasKey(session_name() . '-', $cookies);
        $this->assertNotNull($this->Response()->getHeader('Location'));
    }

    public function testPaymentTokenInvalidRequest(): void
    {
        $this->dispatch('/?swPaymentToken=fooooooo');
        $cookies = $this->Response()->getCookies();

        $this->assertArrayNotHasKey(session_name() . '-', $cookies);
        $this->assertNotNull($this->Response()->getHeader('Location'));
    }
}

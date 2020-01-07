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
        $this->service = Shopware()->Container()->get(\Shopware\Components\Cart\PaymentTokenService::class);
    }

    protected function tearDown(): void
    {
        Shopware()->Container()->get('dbal_connection')->rollBack();
    }

    public function testPaymentTokenStorage(): void
    {
        $hash = $this->service->generate();

        static::assertInstanceOf(PaymentTokenResult::class, $this->service->restore($hash));
        static::assertNull($this->service->restore($hash));
    }

    public function testTokenNotExist(): void
    {
        $hash = 'i dont exist';

        static::assertNull($this->service->restore($hash));
    }

    public function testPaymentTokenValidRequest(): void
    {
        $hash = $this->service->generate();

        $this->dispatch('/?swPaymentToken=' . $hash);
        $cookies = $this->Response()->getCookies();
        $key = session_name() . '-';

        static::assertArrayHasKey($key, $cookies);
        static::assertNotNull($this->Response()->getHeader('Location'));
        static::assertEquals(ini_get('session.cookie_path'), $cookies[$key]['path']);

        $path = Shopware()->Front()->Request()->getBasePath();
        if ($path === '') {
            $path = '/';
        }

        static::assertEquals($path, $cookies[$key]['path']);
    }

    public function paymentTokenProviders(): array
    {
        return [
            // Language shops
            [
                '/de',
                '/',
            ],
            [
                '/en',
                '/',
            ],
            // Language Shop in subfolder
            [
                '/foo/de',
                '/foo',
            ],
            // Shop in subfolder
            [
                '',
                '/foo',
            ],
            // Shop at
            [
                '',
                '/',
            ],
        ];
    }

    /**
     * @dataProvider paymentTokenProviders
     */
    public function testPaymentTokenPath(string $virtualUrl, string $path): void
    {
        session_destroy();
        Shopware()->Container()->reset('session');

        $currentUrl = Shopware()->Shop()->getBaseUrl();
        $currentPath = Shopware()->Shop()->getBasePath();

        Shopware()->Shop()->setBaseUrl($virtualUrl);
        Shopware()->Shop()->setBasePath($path);

        $hash = $this->service->generate();

        $this->dispatch('/?swPaymentToken=' . $hash);
        $cookies = $this->Response()->getCookies();
        $key = session_name() . '-';

        static::assertArrayHasKey($key, $cookies);
        static::assertNotNull($this->Response()->getHeader('Location'));
        static::assertEquals(ini_get('session.cookie_path'), $cookies[$key]['path']);

        Shopware()->Shop()->setBaseUrl($currentUrl);
        Shopware()->Shop()->setBasePath($currentPath);
    }

    public function testPaymentTokenInvalidRequest(): void
    {
        $this->dispatch('/?swPaymentToken=fooooooo');
        $cookies = $this->Response()->getCookies();

        static::assertArrayNotHasKey(session_name() . '-', $cookies);
        static::assertNotNull($this->Response()->getHeader('Location'));
    }
}

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

use PHPUnit\Framework\TestCase;
use Shopware\Tests\Functional\Traits\CustomerLoginTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;
use Shopware\Tests\Functional\Traits\FixtureBehaviour;

class sBasketSAddVoucherTest extends TestCase
{
    use FixtureBehaviour;
    use CustomerLoginTrait;
    use DatabaseTransactionBehaviour;

    public function testsAddVoucherExpiredVoucher(): void
    {
        $voucherCode = 'foobar01';

        $this->executeFixture(__DIR__ . '/fixtures/add-expired-voucher.sql');

        $this->loginCustomer();

        $frontendController = Shopware()->Container()->get('front');
        static::assertInstanceOf(\Enlight_Controller_Front::class, $frontendController);
        $frontendController->setRequest(new \Enlight_Controller_Request_RequestTestCase());

        $basket = Shopware()->Modules()->Basket();

        $result = $basket->sAddVoucher($voucherCode);

        static::assertIsArray($result);
        static::assertTrue($result['sErrorFlag']);
        static::assertSame('Gutschein konnte nicht gefunden werden oder ist nicht mehr gültig', $result['sErrorMessages'][0]);

        $this->logOutCustomer();
    }
}

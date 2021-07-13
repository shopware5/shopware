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

use Shopware\Tests\Functional\Traits\CustomerLoginTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class sAdminSGetUserDataTest extends PHPUnit\Framework\TestCase
{
    use CustomerLoginTrait;
    use DatabaseTransactionBehaviour;

    public function testSGetUserDataWithPreselectedShippingAddress(): void
    {
        $countryId = 21;
        $sql = file_get_contents(__DIR__ . '/fixtures/user_address_change.sql');
        static::assertIsString($sql);
        Shopware()->Container()->get('dbal_connection')->exec($sql);

        $this->loginCustomer(
            'f375fe1b4ad9c6f2458844226831463f',
            3,
            '$2y$10$Z9JAOaS72cvvMfFRS2ObNui8y0LDNy4JisrN/Pd.Vb9spH95LS2g.',
            'unit@test.com',
        );

        $session = Shopware()->Container()->get('session');
        static::assertInstanceOf(\Enlight_Components_Session_Namespace::class, $session);
        $session->offsetSet('checkoutShippingAddressId', $countryId);

        $result = Shopware()->Modules()->Admin()->sGetUserData();
        static::assertIsArray($result);

        $this->logOutCustomer();
        $session->offsetUnset('checkoutShippingAddressId');

        static::assertSame($countryId, $result['shippingaddress']['country']['id']);
        static::assertSame('FooBar, 12', $result['shippingaddress']['street']);
    }
}

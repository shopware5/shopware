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

namespace Shopware\Tests\Functional\Core;

use Enlight_Components_Session_Namespace;
use Enlight_Controller_Request_RequestTestCase;
use PHPUnit\Framework\TestCase;
use Shopware\Models\Customer\Customer;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\CustomerLoginTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class AdminSGetUserDataTest extends TestCase
{
    use ContainerTrait;
    use CustomerLoginTrait;
    use DatabaseTransactionBehaviour;

    public function setUp(): void
    {
        $sql = file_get_contents(__DIR__ . '/fixtures/user_address_change.sql');
        static::assertIsString($sql);

        $this->getContainer()->get('dbal_connection')->executeStatement($sql);
    }

    public function testSGetUserDataWithPreselectedShippingAddress(): void
    {
        $countryId = 21;

        $this->loginCustomer(
            'f375fe1b4ad9c6f2458844226831463f',
            3,
            'unit@test.com',
            '2021-07-09 07:08:11'
        );

        $session = $this->getContainer()->get('session');
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setControllerName('checkout');
        $this->getContainer()->get('front')->setRequest($request);
        static::assertInstanceOf(Enlight_Components_Session_Namespace::class, $session);
        $session->offsetSet('checkoutShippingAddressId', $countryId);

        $result = $this->getContainer()->get('modules')->Admin()->sGetUserData();
        static::assertIsArray($result);

        $this->logOutCustomer();
        $session->offsetUnset('checkoutShippingAddressId');

        static::assertSame($countryId, $result['shippingaddress']['country']['id']);
        static::assertSame('FooBar, 12', $result['shippingaddress']['street']);
    }

    public function testSGetUserDataWithAddressUserIdNotEqualsToUser(): void
    {
        $shippingAddress = 701;

        $sql = 'UPDATE s_user_addresses SET user_id = 1 WHERE id = 701';
        $this->getContainer()->get('dbal_connection')->executeStatement($sql);

        $this->loginCustomer(
            'f375fe1b4ad9c6f2458844226831463f',
            3,
            'unit@test.com',
            '2021-07-09 07:08:11'
        );

        $session = $this->getContainer()->get('session');
        static::assertInstanceOf(Enlight_Components_Session_Namespace::class, $session);
        $session->offsetSet('checkoutShippingAddressId', $shippingAddress);
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setControllerName('checkout');
        $this->getContainer()->get('front')->setRequest($request);
        $result = $this->getContainer()->get('modules')->Admin()->sGetUserData();
        static::assertIsArray($result);

        $this->logOutCustomer();
        $session->offsetUnset('checkoutShippingAddressId');

        static::assertSame($shippingAddress, $result['shippingaddress']['id']);
    }

    public function testMultipleLoginsWithPasswordChange(): void
    {
        $customerId = 3;
        $customerMail = 'unit@test.com';

        $this->loginCustomer(
            '2af1572ba5d04d6cbb916cce10f31d2b',
            $customerId,
            $customerMail,
            '2021-07-09 07:08:11'
        );

        $customerService = $this->getContainer()->get('shopware_account.customer_service');
        $customerRepository = $this->getContainer()->get('models')->getRepository(Customer::class);
        $customer = $customerRepository->find($customerId);

        static::assertInstanceOf(Customer::class, $customer);

        $customer->setPassword('a1197019-546e-445a-8d48-c6813e3381ed');

        $customerService->update($customer);

        /*
         * The password_change_date has been updated now, but the session used
         * still has the old value of '2021-07-09 07:08:11', so sCheckUser
         * should fail.
         */
        static::assertFalse($this->getContainer()->get('modules')->Admin()->sCheckUser());

        $this->loginCustomer(
            '2af1572ba5d04d6cbb916cce10f31d2b',
            $customerId,
            $customerMail,
            $customer->getPasswordChangeDate()->format('Y-m-d H:i:s')
        );

        static::assertTrue($this->getContainer()->get('modules')->Admin()->sCheckUser());
    }
}

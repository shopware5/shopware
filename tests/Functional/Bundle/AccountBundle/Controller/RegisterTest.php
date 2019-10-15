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

namespace Shopware\Tests\Functional\Bundle\AccountBundle\Controller;

use Doctrine\DBAL\Connection;
use Shopware\Models\Customer\Customer;

class RegisterTest extends \Enlight_Components_Test_Controller_TestCase
{
    const TEST_MAIL = 'unittest@mail.com';
    const SAVE_URL = '/register/saveRegister/sTarget/account/sTargetAction/index';
    const CONFIRM_URL_PREFIX = '/register/confirmValidation/sConfirmation/';

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        Shopware()->Container()->reset('router');
    }

    public function setUp(): void
    {
        parent::setUp();
        Shopware()->Container()->get('dbal_connection')->beginTransaction();
        $this->deleteCustomer(self::TEST_MAIL);
        Shopware()->Container()->get('models')->clear();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Shopware()->Container()->get('dbal_connection')->rollback();
        $this->deleteCustomer(self::TEST_MAIL);
        Shopware()->Container()->get('models')->clear();
    }

    public function testSimpleRegistration()
    {
        $this->Request()->setMethod('POST');
        $this->Request()->setPost([
            'register' => [
                'personal' => $this->getPersonalData(),
                'billing' => $this->getBillingData(),
            ],
        ]);

        $this->sendRequestAndAssertCustomer(
            self::TEST_MAIL,
            [
                'firstname' => 'first name',
                'lastname' => 'last name',
                'salutation' => 'mr',
                'email' => self::TEST_MAIL,
            ],
            [
                'street' => 'street',
                'zipcode' => 'zipcode',
                'city' => 'city',
                'country_id' => 2,
            ],
            [
                'street' => 'street',
                'zipcode' => 'zipcode',
                'city' => 'city',
                'country_id' => 2,
            ]
        );
    }

    public function testDoubleOptInRegistration()
    {
        $this->Request()->setMethod('POST');
        $this->Request()->setPost([
            'register' => [
                'personal' => $this->getPersonalData(),
                'billing' => $this->getBillingData(),
            ],
        ]);

        $this->sendRequestAndAssertCustomerWithDoubleOptIn(self::TEST_MAIL);
    }

    public function testRegistrationWithShipping()
    {
        $this->Request()->setMethod('POST');
        $this->Request()->setPost([
            'register' => [
                'personal' => $this->getPersonalData(),
                'billing' => $this->getBillingData([
                    'shippingAddress' => 1,
                ]),
                'shipping' => $this->getShippingData(),
            ],
        ]);

        $this->sendRequestAndAssertCustomer(
            self::TEST_MAIL,
            ['firstname' => 'first name'],
            ['street' => 'street'],
            [
                'salutation' => 'ms',
                'company' => 'company',
                'department' => 'department',
                'firstname' => 'second first name',
                'lastname' => 'second last name',
                'street' => 'street 2',
                'zipcode' => 'zipcode 2',
                'city' => 'city 2',
                'country_id' => 3,
            ]
        );
    }

    public function testCompanyRegistration()
    {
        $this->Request()->setMethod('POST');
        $this->Request()->setPost([
            'register' => [
                'personal' => $this->getPersonalData([
                    'customer_type' => Customer::CUSTOMER_TYPE_BUSINESS,
                ]),
                'billing' => $this->getBillingData([
                    'vatId' => 'xxxxxxxxxxxxxx',
                    'company' => 'company',
                    'department' => 'department',
                ]),
            ],
        ]);

        $this->sendRequestAndAssertCustomer(
            self::TEST_MAIL,
            [
                'firstname' => 'first name',
            ],
            [
                'street' => 'street',
                'ustid' => 'xxxxxxxxxxxxxx',
                'company' => 'company',
                'department' => 'department',
            ]
        );
    }

    public function testFastRegistration()
    {
        $this->Request()->setMethod('POST');
        $this->Request()->setPost([
            'register' => [
                'personal' => $this->getPersonalData([
                    'password' => null,
                    'accountmode' => Customer::ACCOUNT_MODE_FAST_LOGIN,
                ]),
                'billing' => $this->getBillingData(),
            ],
        ]);

        $this->sendRequestAndAssertCustomer(
            self::TEST_MAIL,
            [
                'firstname' => 'first name',
                'lastname' => 'last name',
                'salutation' => 'mr',
                'email' => self::TEST_MAIL,
                'accountmode' => 1,
            ],
            [
                'street' => 'street',
                'zipcode' => 'zipcode',
                'city' => 'city',
                'country_id' => 2,
            ]
        );
    }

    public function testDefaultPayment()
    {
        Shopware()->Session()->offsetSet('sPaymentID', 6);

        $this->Request()->setMethod('POST');
        $this->Request()->setPost([
            'register' => [
                'personal' => $this->getPersonalData(),
                'billing' => $this->getBillingData(),
            ],
        ]);

        $this->sendRequestAndAssertCustomer(
            self::TEST_MAIL,
            [
                'firstname' => 'first name',
                'paymentID' => 6,
                'lastname' => 'last name',
                'salutation' => 'mr',
                'email' => self::TEST_MAIL,
            ],
            [
                'street' => 'street',
                'zipcode' => 'zipcode',
                'city' => 'city',
                'country_id' => 2,
            ]
        );
    }

    private function sendRequestAndAssertCustomer($email, $personal, $billing = [], $shipping = [])
    {
        $this->doubleOptinSet(false);
        $response = $this->dispatch(self::SAVE_URL);

        static::assertEquals(302, $response->getHttpResponseCode());

        static::assertStringEndsWith(
            '/account',
            $this->getHeaderLocation($response)
        );

        $session = Shopware()->Container()->get('session');
        static::assertNotEmpty($session->offsetGet('sUserId'));

        $customer = Shopware()->Container()->get('dbal_connection')->fetchAssoc(
            'SELECT * FROM s_user WHERE email = :mail AND active = 1 AND doubleOptinEmailSentDate IS NULL LIMIT 1',
            [':mail' => $email]
        );
        static::assertNotEmpty($customer);

        if (!empty($personal)) {
            foreach ($personal as $key => $value) {
                static::assertArrayHasKey($key, $customer);
                static::assertEquals($value, $customer[$key]);
            }
        }

        if (!empty($billing)) {
            $this->assertAddress($email, $billing);
        }

        if (!empty($shipping)) {
            $this->assertAddress($email, $shipping, 'shipping');
        }
    }

    private function sendRequestAndAssertCustomerWithDoubleOptIn($email)
    {
        $this->doubleOptinSet(true);
        $this->dispatch(self::SAVE_URL);

        /** @var Connection $connection */
        $connection = Shopware()->Container()->get('dbal_connection');

        $customer = $connection->fetchAssoc(
            'SELECT id, doubleOptinEmailSentDate FROM s_user WHERE email = :mail AND active = 0 AND doubleOptinEmailSentDate IS NOT NULL LIMIT 1',
            [':mail' => $email]
        );

        static::assertNotEmpty($customer);

        $optin = $connection->fetchAssoc(
            'SELECT type, data, hash FROM s_core_optin WHERE datum = :datum LIMIT 1',
            [':datum' => $customer['doubleOptinEmailSentDate']]
        );

        static::assertNotEmpty($optin);
        static::assertEquals('swRegister', $optin['type']);
        static::assertNotEmpty($optin['data']);

        $data = unserialize($optin['data']);
        static::assertEquals($customer['id'], $data['customerId']);
        $this->sendRequestAndAssertDOIConfirmation($email, $optin['hash']);
    }

    private function sendRequestAndAssertDOIConfirmation($email, $hash)
    {
        /** @var Connection $connection */
        $connection = Shopware()->Container()->get('dbal_connection');

        // Create broken data
        $connection->executeQuery(
            'INSERT INTO s_core_optin (type, datum, hash, data)
             SELECT type, (datum - INTERVAL 1 MINUTE), CONCAT(hash,\'X\'), \'I am definitly not working\' 
             FROM s_core_optin
             WHERE hash = :hash',
            [':hash' => $hash]
        );

        $this->reset();
        $this->doubleOptinSet(true);
        $this->dispatch(self::CONFIRM_URL_PREFIX . $hash);

        $customer = $connection->fetchAssoc(
            'SELECT doubleOptinEmailSentDate FROM s_user WHERE email = :mail AND active = 1 AND doubleOptinEmailSentDate IS NOT NULL AND doubleOptinConfirmDate IS NOT NULL LIMIT 1',
            [':mail' => $email]
        );
        static::assertNotEmpty($customer);

        $optin = $connection->fetchAssoc(
            'SELECT * FROM s_core_optin WHERE datum = :datum LIMIT 1',
            [':datum' => $customer['doubleOptinEmailSentDate']]
        );
        static::assertEmpty($optin);

        // Test broken data
        $this->reset();
        $this->doubleOptinSet(true);
        $this->expectException(\InvalidArgumentException::class);
        $this->dispatch(self::CONFIRM_URL_PREFIX . $hash . 'X');
    }

    private function deleteCustomer($email)
    {
        Shopware()->Container()->get('dbal_connection')->executeQuery(
            'DELETE FROM s_user WHERE email = :mail',
            [':mail' => $email]
        );
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private function getPersonalData($data = [])
    {
        return array_merge([
            'salutation' => 'mr',
            'customer_type' => Customer::CUSTOMER_TYPE_PRIVATE,
            'password' => 'defaultpassword',
            'email' => self::TEST_MAIL,
            'firstname' => 'first name',
            'lastname' => 'last name',
            'accountmode' => Customer::ACCOUNT_MODE_CUSTOMER,
        ], $data);
    }

    private function getShippingData($data = [])
    {
        return array_merge([
            'salutation' => 'ms',
            'company' => 'company',
            'department' => 'department',
            'firstname' => 'second first name',
            'lastname' => 'second last name',
            'street' => 'street 2',
            'zipcode' => 'zipcode 2',
            'city' => 'city 2',
            'country' => 3,
        ], $data);
    }

    private function getBillingData($data = [])
    {
        return array_merge([
            'street' => 'street',
            'zipcode' => 'zipcode',
            'city' => 'city',
            'country' => 2,
            'country_state_2' => 6,
        ], $data);
    }

    /**
     * @param string $email
     * @param array  $data
     * @param string $type
     */
    private function assertAddress($email, $data, $type = 'billing')
    {
        $column = 'default_billing_address_id';
        if ($type !== 'billing') {
            $column = 'default_shipping_address_id';
        }

        $address = Shopware()->Container()->get('dbal_connection')->fetchAssoc(
            'SELECT address.* FROM s_user_addresses address, s_user user WHERE user.' . $column . ' = address.id AND user.email = :mail',
            [':mail' => $email]
        );

        static::assertNotEmpty($address);

        foreach ($data as $key => $value) {
            static::assertArrayHasKey($key, $address);
            static::assertEquals($value, $address[$key]);
        }
    }

    /**
     * @return string|null
     */
    private function getHeaderLocation(\Enlight_Controller_Response_Response $response)
    {
        $headers = $response->getHeaders();
        foreach ($headers as $header) {
            if ($header['name'] == 'Location') {
                return $header['value'];
            }
        }

        return null;
    }

    /**
     * @param bool $switch
     */
    private function doubleOptinSet($switch)
    {
        Shopware()->Container()->get('config_writer')->save('optinregister', $switch, null, Shopware()->Shop()->getId());
        Shopware()->Container()->get('config')->setShop(Shopware()->Shop());
        Shopware()->Container()->get('cache')->clean();
    }
}

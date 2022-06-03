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

namespace Shopware\Tests\Functional\Bundle\AccountBundle\Controller;

use Doctrine\DBAL\Connection;
use Enlight_Components_Test_Controller_TestCase as ControllerTestCase;
use Enlight_Controller_Response_Response;
use InvalidArgumentException;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Customer\Customer;
use Shopware\Tests\Functional\Traits\ContainerTrait;

class RegisterTest extends ControllerTestCase
{
    use ContainerTrait;

    public const TEST_MAIL = 'unittest@mail.com';
    public const SAVE_URL = '/register/saveRegister/sTarget/account/sTargetAction/index';
    public const CONFIRM_URL_PREFIX = '/register/confirmValidation/sConfirmation/';

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        Shopware()->Container()->reset('router');
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->getContainer()->get(Connection::class)->beginTransaction();
        $this->deleteCustomer();
        $this->getContainer()->get(ModelManager::class)->clear();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->getContainer()->get(Connection::class)->rollBack();
        $this->deleteCustomer();
        $this->getContainer()->get(ModelManager::class)->clear();
    }

    public function testSimpleRegistration(): void
    {
        $this->Request()->setMethod('POST');
        $this->Request()->setPost([
            'register' => [
                'personal' => $this->getPersonalData(),
                'billing' => $this->getBillingData(),
            ],
        ]);

        $this->sendRequestAndAssertCustomer(
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

    public function testDoubleOptInRegistration(): void
    {
        $this->Request()->setMethod('POST');
        $this->Request()->setPost([
            'register' => [
                'personal' => $this->getPersonalData(),
                'billing' => $this->getBillingData(),
            ],
        ]);

        $this->sendRequestAndAssertCustomerWithDoubleOptIn();
    }

    public function testRegistrationWithShipping(): void
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

    public function testCompanyRegistration(): void
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

    public function testFastRegistration(): void
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

    public function testDefaultPayment(): void
    {
        $this->getContainer()->get('session')->offsetSet('sPaymentID', 6);

        $this->Request()->setMethod('POST');
        $this->Request()->setPost([
            'register' => [
                'personal' => $this->getPersonalData(),
                'billing' => $this->getBillingData(),
            ],
        ]);

        $this->sendRequestAndAssertCustomer(
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

    /**
     * @param array<string, mixed> $personal
     * @param array<string, mixed> $billing
     * @param array<string, mixed> $shipping
     */
    private function sendRequestAndAssertCustomer(array $personal, array $billing = [], array $shipping = []): void
    {
        $this->doubleOptInSet(false);
        $response = $this->dispatch(self::SAVE_URL);

        static::assertEquals(302, $response->getHttpResponseCode());

        static::assertStringEndsWith(
            '/account',
            $this->getHeaderLocation($response) ?? ''
        );

        $session = $this->getContainer()->get('session');
        static::assertNotEmpty($session->get('sUserId'));

        $customer = $this->getContainer()->get(Connection::class)->fetchAssociative(
            'SELECT * FROM s_user WHERE email = :mail AND active = 1 AND doubleOptinEmailSentDate IS NULL LIMIT 1',
            [':mail' => self::TEST_MAIL]
        );
        static::assertIsArray($customer);

        if (!empty($personal)) {
            foreach ($personal as $key => $value) {
                static::assertArrayHasKey($key, $customer);
                static::assertEquals($value, $customer[$key]);
            }
        }

        if (!empty($billing)) {
            $this->assertAddress(self::TEST_MAIL, $billing);
        }

        if (!empty($shipping)) {
            $this->assertAddress(self::TEST_MAIL, $shipping, 'shipping');
        }
    }

    private function sendRequestAndAssertCustomerWithDoubleOptIn(): void
    {
        $this->doubleOptInSet(true);
        $this->dispatch(self::SAVE_URL);

        $connection = $this->getContainer()->get(Connection::class);

        $customer = $connection->fetchAssociative(
            'SELECT id, doubleOptinEmailSentDate FROM s_user WHERE email = :mail AND active = 0 AND doubleOptinEmailSentDate IS NOT NULL LIMIT 1',
            [':mail' => self::TEST_MAIL]
        );

        static::assertIsArray($customer);

        $optIn = $connection->fetchAssociative(
            'SELECT type, data, hash FROM s_core_optin WHERE datum = :datum LIMIT 1',
            [':datum' => $customer['doubleOptinEmailSentDate']]
        );
        static::assertIsArray($optIn);

        static::assertNotEmpty($optIn);
        static::assertEquals('swRegister', $optIn['type']);
        static::assertNotEmpty($optIn['data']);

        $data = unserialize($optIn['data']);
        static::assertEquals($customer['id'], $data['customerId']);
        $this->sendRequestAndAssertDOIConfirmation($optIn['hash']);
    }

    private function sendRequestAndAssertDOIConfirmation(string $hash): void
    {
        $connection = $this->getContainer()->get(Connection::class);

        // Create broken data
        $connection->executeQuery(
            'INSERT INTO s_core_optin (type, datum, hash, data)
             SELECT type, (datum - INTERVAL 1 MINUTE), CONCAT(hash,\'X\'), \'I am definitely not working\'
             FROM s_core_optin
             WHERE hash = :hash',
            [':hash' => $hash]
        );

        $this->reset();
        $this->doubleOptInSet(true);
        $this->dispatch(self::CONFIRM_URL_PREFIX . $hash);

        $customer = $connection->fetchAssociative(
            'SELECT doubleOptinEmailSentDate FROM s_user WHERE email = :mail AND active = 1 AND doubleOptinEmailSentDate IS NOT NULL AND doubleOptinConfirmDate IS NOT NULL LIMIT 1',
            [':mail' => self::TEST_MAIL]
        );
        static::assertIsArray($customer);

        $optIn = $connection->fetchAssociative(
            'SELECT * FROM s_core_optin WHERE datum = :datum LIMIT 1',
            [':datum' => $customer['doubleOptinEmailSentDate']]
        );
        static::assertFalse($optIn);

        // Test broken data
        $this->reset();
        $this->doubleOptInSet(true);
        $this->expectException(InvalidArgumentException::class);
        $this->dispatch(self::CONFIRM_URL_PREFIX . $hash . 'X');
    }

    private function deleteCustomer(): void
    {
        $this->getContainer()->get(Connection::class)->executeQuery(
            'DELETE FROM s_user WHERE email = :mail',
            [':mail' => self::TEST_MAIL]
        );
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private function getPersonalData(array $data = []): array
    {
        return array_merge([
            'salutation' => 'mr',
            'customer_type' => Customer::CUSTOMER_TYPE_PRIVATE,
            'password' => 'defaultPassword',
            'email' => self::TEST_MAIL,
            'firstname' => 'first name',
            'lastname' => 'last name',
            'accountmode' => Customer::ACCOUNT_MODE_CUSTOMER,
        ], $data);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private function getShippingData(array $data = []): array
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

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private function getBillingData(array $data = []): array
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
     * @param array<string, mixed> $data
     */
    private function assertAddress(string $email, array $data, string $type = 'billing'): void
    {
        $column = 'default_billing_address_id';
        if ($type !== 'billing') {
            $column = 'default_shipping_address_id';
        }

        $address = $this->getContainer()->get(Connection::class)->fetchAssociative(
            'SELECT address.* FROM s_user_addresses address, s_user user WHERE user.' . $column . ' = address.id AND user.email = :mail',
            [':mail' => $email]
        );

        static::assertIsArray($address);

        foreach ($data as $key => $value) {
            static::assertArrayHasKey($key, $address);
            static::assertEquals($value, $address[$key]);
        }
    }

    private function getHeaderLocation(Enlight_Controller_Response_Response $response): ?string
    {
        foreach ($response->getHeaders() as $header) {
            if ($header['name'] === 'Location') {
                return $header['value'];
            }
        }

        return null;
    }

    private function doubleOptInSet(bool $switch): void
    {
        $this->setConfig('optinregister', $switch);
    }
}

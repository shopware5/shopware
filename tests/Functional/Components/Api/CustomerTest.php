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

namespace Shopware\Tests\Functional\Components\Api;

use DateTime;
use Doctrine\DBAL\Connection;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Components\Api\Exception\NotFoundException;
use Shopware\Components\Api\Exception\ParameterMissingException;
use Shopware\Components\Api\Exception\ValidationException;
use Shopware\Components\Api\Resource\Customer as CustomerResource;
use Shopware\Components\Api\Resource\Resource;
use Shopware\Components\Random;
use Shopware\Models\Attribute\Customer as CustomerAttribute;
use Shopware\Models\Attribute\CustomerAddress;
use Shopware\Models\Customer\Address;
use Shopware\Models\Customer\Customer;
use Shopware\Models\Customer\Group;

class CustomerTest extends TestCase
{
    /**
     * @var CustomerResource
     */
    protected $resource;

    protected function setUp(): void
    {
        parent::setUp();
        Shopware()->Container()->get(Connection::class)->executeStatement('UPDATE s_core_countries SET allow_shipping = 0 WHERE id = 25');
    }

    /**
     * @return CustomerResource
     */
    public function createResource()
    {
        return new CustomerResource();
    }

    public function testCreateWithNonUniqueEmailShouldThrowException(): void
    {
        $this->expectException(ValidationException::class);
        $testData = [
            'password' => 'fooobar',
            'active' => true,
            'email' => 'test@example.com',

            'billing' => [
                'salutation' => 'mr',
                'zipcode' => '12345',
                'city' => 'Musterhausen',
                'firstname' => 'Max',
                'lastname' => 'Mustermann',
                'street' => 'Musterstr. 123',
                'additionalAddressLine1' => 'Address Billing Addition 1',
                'additionalAddressLine2' => 'Address Billing Addition 2',
                'country' => '2',
                'attribute' => [
                    'text1' => 'Freitext1',
                    'text2' => 'Freitext2',
                ],
            ],

            'shipping' => [
                'salutation' => 'Mr',
                'company' => 'Widgets Inc.',
                'firstname' => 'Max',
                'lastname' => 'Mustermann',
                'additionalAddressLine1' => 'Address Shipping Addition 1',
                'additionalAddressLine2' => 'Address Shipping Addition 2',
                'country' => '2',
                'street' => 'Musterstr. 123',
                'zipcode' => '12345',
                'city' => 'Mustercity',
                'attribute' => [
                    'text1' => 'Freitext1',
                    'text2' => 'Freitext2',
                ],
            ],
        ];

        $this->resource->create($testData);
    }

    public function testCreateShouldBeSuccessful(): int
    {
        $date = new DateTime();
        $date->modify('-10 days');
        $firstLogin = $date->format(DateTime::ISO8601);

        $date->modify('+2 day');
        $lastLogin = $date->format(DateTime::ISO8601);

        $date = DateTime::createFromFormat('Y-m-d', '1986-12-20');
        static::assertInstanceOf(DateTime::class, $date);
        $birthday = $date->format(DateTime::ISO8601);

        $testData = [
            'password' => 'fooobar',
            'email' => Random::getAlphanumericString(5) . 'test@foobar.com',
            'number' => 'testnumber' . uniqid(),
            'firstlogin' => $firstLogin,
            'lastlogin' => $lastLogin,

            'salutation' => 'mr',
            'firstname' => 'Max',
            'lastname' => 'Mustermann',
            'birthday' => $birthday,

            'billing' => [
                'salutation' => 'mr',
                'zipcode' => '12345',
                'city' => 'Musterhausen',
                'firstname' => 'Max',
                'lastname' => 'Mustermann',
                'street' => 'Musterstr. 123',
                'additionalAddressLine1' => 'Address Billing Addition 1',
                'additionalAddressLine2' => 'Address Billing Addition 2',
                'country' => '2',
                'attribute' => [
                    'text1' => 'Freitext1',
                    'text2' => 'Freitext2',
                ],
            ],

            'shipping' => [
                'salutation' => 'Mr',
                'company' => 'Widgets Inc.',
                'firstname' => 'Max',
                'lastname' => 'Mustermann',
                'additionalAddressLine1' => 'Address Shipping Addition 1',
                'additionalAddressLine2' => 'Address Shipping Addition 2',
                'country' => '2',
                'street' => 'Musterstr. 123',
                'zipcode' => '12345',
                'city' => 'Mustercity',
                'attribute' => [
                    'text1' => 'Freitext1',
                    'text2' => 'Freitext2',
                ],
            ],

            'debit' => [
                'account' => 'Fake Account',
                'bankCode' => '55555555',
                'bankName' => 'Fake Bank',
                'accountHolder' => 'Max Mustermann',
            ],
        ];

        $customer = $this->resource->create($testData);

        static::assertInstanceOf(Customer::class, $customer);
        static::assertGreaterThan(0, $customer->getId());

        // Test default values
        static::assertEquals(1, $customer->getShop()->getId());
        static::assertEquals(0, $customer->getAccountMode());
        static::assertInstanceOf(Group::class, $customer->getGroup());
        static::assertEquals('EK', $customer->getGroup()->getKey());
        static::assertTrue($customer->getActive());

        static::assertEquals($customer->getEmail(), $testData['email']);

        static::assertInstanceOf(Address::class, $customer->getDefaultBillingAddress());
        static::assertInstanceOf(Address::class, $customer->getDefaultShippingAddress());
        static::assertInstanceOf(CustomerAddress::class, $customer->getDefaultBillingAddress()->getAttribute());
        static::assertInstanceOf(CustomerAddress::class, $customer->getDefaultShippingAddress()->getAttribute());

        static::assertEquals($customer->getDefaultBillingAddress()->getFirstname(), $testData['billing']['firstname']);
        static::assertEquals($customer->getDefaultShippingAddress()->getFirstname(), $testData['shipping']['firstname']);

        static::assertEquals($customer->getDefaultBillingAddress()->getAttribute()->getText1(), $testData['billing']['attribute']['text1']);
        static::assertEquals($customer->getDefaultShippingAddress()->getAttribute()->getText1(), $testData['shipping']['attribute']['text1']);

        // test additional address lines
        static::assertEquals($customer->getDefaultShippingAddress()->getAdditionalAddressLine1(), $testData['shipping']['additionalAddressLine1']);
        static::assertEquals($customer->getDefaultShippingAddress()->getAdditionalAddressLine2(), $testData['shipping']['additionalAddressLine2']);
        static::assertEquals($customer->getDefaultBillingAddress()->getAdditionalAddressLine1(), $testData['billing']['additionalAddressLine1']);
        static::assertEquals($customer->getDefaultBillingAddress()->getAdditionalAddressLine2(), $testData['billing']['additionalAddressLine2']);

        return $customer->getId();
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetOneShouldBeSuccessful(int $id): void
    {
        $customer = $this->resource->getOne($id);
        static::assertIsArray($customer);
        static::assertGreaterThan(0, $customer['id']);
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetOneByNumberShouldBeSuccessful(int $id): void
    {
        $this->resource->setResultMode(Resource::HYDRATE_OBJECT);
        $customer = $this->resource->getOne($id);
        static::assertInstanceOf(Customer::class, $customer);
        $number = $customer->getNumber();
        static::assertIsString($number);

        $customer = $this->resource->getOneByNumber($number);
        static::assertInstanceOf(Customer::class, $customer);
        static::assertEquals($id, $customer->getId());
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetOneShouldBeAbleToReturnObject(int $id): void
    {
        $this->resource->setResultMode(Resource::HYDRATE_OBJECT);
        $customer = $this->resource->getOne($id);

        static::assertInstanceOf(Customer::class, $customer);
        static::assertGreaterThan(0, $customer->getId());
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetListShouldBeSuccessful(): void
    {
        $result = $this->resource->getList();

        static::assertArrayHasKey('data', $result);
        static::assertArrayHasKey('total', $result);

        static::assertGreaterThanOrEqual(1, $result['total']);
        static::assertGreaterThanOrEqual(1, $result['data']);
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetListShouldBeAbleToReturnObjects(): void
    {
        $this->resource->setResultMode(Resource::HYDRATE_OBJECT);
        $result = $this->resource->getList();

        static::assertArrayHasKey('data', $result);
        static::assertArrayHasKey('total', $result);

        static::assertGreaterThanOrEqual(1, $result['total']);
        static::assertGreaterThanOrEqual(1, $result['data']);

        static::assertInstanceOf(Customer::class, $result['data'][0]);
    }

    public function testCreateWithInvalidDataShouldThrowValidationException(): void
    {
        $this->expectException(ValidationException::class);
        $testData = [
            'active' => true,
            'email' => 'invalid',
            'billing' => [
                'firstname' => 'Max',
                'lastname' => 'Mustermann',
                'country' => 2,
            ],
        ];

        $this->resource->create($testData);
    }

    public function testCreateWithInvalidDataShouldThrowValidationExceptionWithCorrectToStringMessage(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageMatches('"salutation: The value you selected is not a valid choice."');
        $testData = [
            'active' => true,
            'firstname' => 'Max',
            'lastname' => 'Mustermann',
            'email' => 'max@mustermann.de',
            'salutation' => 'max',
            'billing' => [
                'firstname' => 'Max',
                'lastname' => 'Mustermann',
                'country' => 2,
            ],
        ];

        $this->resource->create($testData);
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testUpdateShouldBeSuccessful(int $id): int
    {
        $testData = [
            'active' => true,
            'email' => Random::getAlphanumericString(5) . 'update@foobar.com',
            'billing' => [
                'firstname' => 'Max Update',
                'lastname' => 'Mustermann Update',
                'additionalAddressLine1' => 'additional billing address Line 1',
                'additionalAddressLine2' => 'additional billing address Line 2',
            ],
            'shipping' => [
                'additionalAddressLine1' => 'additional shipping address Line 1',
                'additionalAddressLine2' => 'additional shipping address Line 2',
            ],
        ];

        $customer = $this->resource->update($id, $testData);

        static::assertInstanceOf(Customer::class, $customer);
        static::assertEquals($id, $customer->getId());

        static::assertEquals($customer->getEmail(), $testData['email']);
        static::assertInstanceOf(Address::class, $customer->getDefaultBillingAddress());
        static::assertInstanceOf(Address::class, $customer->getDefaultShippingAddress());
        static::assertEquals($customer->getDefaultBillingAddress()->getFirstname(), $testData['billing']['firstname']);

        // test additional fields
        static::assertEquals($customer->getDefaultBillingAddress()->getAdditionalAddressLine1(), $testData['billing']['additionalAddressLine1']);
        static::assertEquals($customer->getDefaultBillingAddress()->getAdditionalAddressLine2(), $testData['billing']['additionalAddressLine2']);

        static::assertEquals($customer->getDefaultShippingAddress()->getAdditionalAddressLine1(), $testData['shipping']['additionalAddressLine1']);
        static::assertEquals($customer->getDefaultShippingAddress()->getAdditionalAddressLine2(), $testData['shipping']['additionalAddressLine2']);

        return $id;
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testUpdateByNumberShouldBeSuccessful(int $id): ?string
    {
        $this->resource->setResultMode(Resource::HYDRATE_OBJECT);
        $customer = $this->resource->getOne($id);
        static::assertInstanceOf(Customer::class, $customer);
        $number = $customer->getNumber();
        static::assertIsString($number);

        $testData = [
            'active' => true,
            'email' => Random::getAlphanumericString(5) . 'update@foobar.com',
            'billing' => [
                'firstname' => 'Max Update',
                'lastname' => 'Mustermann Update',
            ],
        ];

        $customer = $this->resource->updateByNumber($number, $testData);

        static::assertInstanceOf(Customer::class, $customer);
        static::assertEquals($id, $customer->getId());

        static::assertEquals($customer->getEmail(), $testData['email']);
        static::assertInstanceOf(Address::class, $customer->getDefaultBillingAddress());
        static::assertEquals($customer->getDefaultBillingAddress()->getFirstname(), $testData['billing']['firstname']);

        return $number;
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testUpdateWithInvalidDataShouldThrowValidationException(int $id): void
    {
        $this->expectException(ValidationException::class);
        $testData = [
            'active' => true,
            'email' => 'invalid',
            'billing' => [
                'firstname' => 'Max',
                'lastname' => 'Mustermann',
            ],
        ];

        $this->resource->update($id, $testData);
    }

    public function testUpdateWithInvalidIdShouldThrowNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);
        $this->resource->update(9999999, []);
    }

    public function testUpdateWithMissingIdShouldThrowParameterMissingException(): void
    {
        $this->expectException(ParameterMissingException::class);
        $this->resource->update(0, []);
    }

    /**
     * @depends testUpdateShouldBeSuccessful
     */
    public function testDeleteShouldBeSuccessful(int $id): void
    {
        $customer = $this->resource->delete($id);

        static::assertInstanceOf(Customer::class, $customer);
        static::assertSame(0, (int) $customer->getId());
    }

    public function testDeleteWithInvalidIdShouldThrowNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);
        $this->resource->delete(9999999);
    }

    public function testDeleteWithMissingIdShouldThrowParameterMissingException(): void
    {
        $this->expectException(ParameterMissingException::class);
        $this->resource->delete(0);
    }

    public function testPostCustomersWithDebitShouldCreatePaymentData(): void
    {
        $date = new DateTime();
        $date->modify('-10 days');
        $firstLogin = $date->format(DateTime::ISO8601);

        $date->modify('+2 day');
        $lastLogin = $date->format(DateTime::ISO8601);

        $date = DateTime::createFromFormat('Y-m-d', '1986-12-20');
        static::assertInstanceOf(DateTime::class, $date);
        $birthday = $date->format(DateTime::ISO8601);

        $requestData = [
            'password' => 'fooobar',
            'active' => true,
            'email' => Random::getAlphanumericString(5) . 'test1@foobar.com',

            'firstlogin' => $firstLogin,
            'lastlogin' => $lastLogin,

            'salutation' => 'mr',
            'firstname' => 'Max',
            'lastname' => 'Mustermann',
            'birthday' => $birthday,

            'billing' => [
                'salutation' => 'mr',
                'firstname' => 'Max',
                'lastname' => 'Mustermann',
                'street' => 'Musterstraße 123',
                'zipcode' => '12345',
                'city' => 'Musterhausen',
                'country' => '2',
            ],

            'shipping' => [
                'salutation' => 'Mr',
                'company' => 'Widgets Inc.',
                'firstname' => 'Max',
                'lastname' => 'Mustermann',
                'street' => 'Musterstraße 123',
                'zipcode' => '12345',
                'city' => 'Musterhausen',
                'country' => '2',
            ],

            'debit' => [
                'account' => 'Fake Account',
                'bankCode' => '55555555',
                'bankName' => 'Fake Bank',
                'accountHolder' => 'Max Mustermann',
            ],
        ];

        $customer = $this->resource->create($requestData);
        static::assertInstanceOf(Customer::class, $customer);
        $identifier = $customer->getId();

        $this->resource->getManager()->clear();
        $customer = Shopware()->Models()->getRepository(Customer::class)->find($identifier);
        static::assertInstanceOf(Customer::class, $customer);

        $paymentDataArray = $customer->getPaymentData()->toArray();
        $paymentData = array_shift($paymentDataArray);

        static::assertNotNull($paymentData);
        static::assertEquals('Max Mustermann', $paymentData->getAccountHolder());
        static::assertEquals('Fake Account', $paymentData->getAccountNumber());
        static::assertEquals('Fake Bank', $paymentData->getBankName());
        static::assertEquals('55555555', $paymentData->getBankCode());

        $this->testDeleteShouldBeSuccessful($identifier);
    }

    public function testPostCustomersWithDebitPaymentDataShouldCreateDebitData(): void
    {
        $date = new DateTime();
        $date->modify('-10 days');
        $firstLogin = $date->format(DateTime::ISO8601);

        $date->modify('+2 day');
        $lastLogin = $date->format(DateTime::ISO8601);

        $date = DateTime::createFromFormat('Y-m-d', '1986-12-20');
        static::assertInstanceOf(DateTime::class, $date);
        $birthday = $date->format(DateTime::ISO8601);

        $requestData = [
            'password' => 'fooobar',
            'active' => true,
            'email' => Random::getAlphanumericString(5) . 'test2@foobar.com',

            'firstlogin' => $firstLogin,
            'lastlogin' => $lastLogin,

            'salutation' => 'mr',
            'firstname' => 'Max',
            'lastname' => 'Mustermann',
            'birthday' => $birthday,

            'billing' => [
                'salutation' => 'mr',
                'firstname' => 'Max',
                'lastname' => 'Mustermann',
                'street' => 'Musterstraße 123',
                'zipcode' => '12345',
                'city' => 'Musterhausen',
                'country' => '2',
            ],

            'shipping' => [
                'salutation' => 'Mr',
                'company' => 'Widgets Inc.',
                'firstname' => 'Max',
                'lastname' => 'Mustermann',
                'street' => 'Musterstraße 123',
                'zipcode' => '12345',
                'city' => 'Musterhausen',
                'country' => '2',
            ],

            'paymentData' => [
                [
                    'paymentMeanId' => 2,
                    'accountNumber' => 'Fake Account',
                    'bankCode' => '55555555',
                    'bankName' => 'Fake Bank',
                    'accountHolder' => 'Max Mustermann',
                ],
            ],
        ];

        $customer = $this->resource->create($requestData);
        $identifier = $customer->getId();

        $this->resource->getManager()->clear();
        $customer = Shopware()->Models()->getRepository(Customer::class)->find($identifier);
        static::assertInstanceOf(Customer::class, $customer);

        $paymentDataArray = $customer->getPaymentData()->toArray();
        $paymentData = array_shift($paymentDataArray);

        static::assertNotNull($paymentData);
        static::assertEquals('Max Mustermann', $paymentData->getAccountHolder());
        static::assertEquals('Fake Account', $paymentData->getAccountNumber());
        static::assertEquals('Fake Bank', $paymentData->getBankName());
        static::assertEquals('55555555', $paymentData->getBankCode());

        $this->testDeleteShouldBeSuccessful($identifier);
    }

    public function testCreateWithDifferentCustomerGroup(): void
    {
        $data = [
            'password' => 'fooobar',
            'email' => __FUNCTION__ . Random::getAlphanumericString(5) . '@foobar.com',
            'number' => __FUNCTION__,
            'salutation' => 'mr',
            'firstname' => 'Max',
            'lastname' => 'Mustermann',
            'groupKey' => 'H',
            'billing' => [
                'salutation' => 'mr',
                'zipcode' => '12345',
                'city' => 'Musterhausen',
                'firstname' => 'Max',
                'lastname' => 'Mustermann',
                'street' => 'Musterstr. 123',
                'country' => '2',
            ],
        ];

        $customer = $this->resource->create($data);
        static::assertInstanceOf(Group::class, $customer->getGroup());
        static::assertEquals('H', $customer->getGroup()->getKey());
    }

    public function testCreateCustomerWithDefaultShopCustomerGroup(): void
    {
        $context = Shopware()->Container()->get(ContextServiceInterface::class)->createShopContext(1);
        $data = [
            'shopId' => 1,
            'password' => 'fooobar',
            'email' => __FUNCTION__ . Random::getAlphanumericString(5) . '@foobar.com',
            'number' => __FUNCTION__,
            'salutation' => 'mr',
            'firstname' => 'Max',
            'lastname' => 'Mustermann',
            'billing' => [
                'salutation' => 'mr',
                'zipcode' => '12345',
                'city' => 'Musterhausen',
                'firstname' => 'Max',
                'lastname' => 'Mustermann',
                'street' => 'Musterstr. 123',
                'country' => '2',
            ],
        ];

        $customer = $this->resource->create($data);
        static::assertInstanceOf(Group::class, $customer->getGroup());
        static::assertEquals($context->getShop()->getCustomerGroup()->getKey(), $customer->getGroup()->getKey());
    }

    /**
     * @group failing
     */
    public function testCreateCustomerCreatesCustomerAttribute(): void
    {
        $data = [
            'email' => __FUNCTION__ . Random::getAlphanumericString(5) . '@foobar.com',
            'number' => __FUNCTION__,
            'salutation' => 'mr',
            'firstname' => 'Max',
            'lastname' => 'Mustermann',
            'billing' => [
                'salutation' => 'mr',
                'zipcode' => '12345',
                'city' => 'Musterhausen',
                'firstname' => 'Max',
                'lastname' => 'Mustermann',
                'street' => 'Musterstr. 123',
                'country' => '2',
            ],
        ];

        $customer = $this->resource->create($data);

        static::assertNotNull($customer->getAttribute());
        static::assertInstanceOf(CustomerAttribute::class, $customer->getAttribute());
    }
}

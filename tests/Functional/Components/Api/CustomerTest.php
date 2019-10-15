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

namespace Shopware\Tests\Functional\Components\Api;

use Shopware\Components\Api\Resource\Address;
use Shopware\Components\Api\Resource\Customer;
use Shopware\Components\Api\Resource\Resource;
use Shopware\Components\Random;
use Shopware\Models\Attribute\Customer as CustomerAttribute;

class CustomerTest extends TestCase
{
    /**
     * @var Customer
     */
    protected $resource;

    protected function setUp(): void
    {
        parent::setUp();
        Shopware()->Container()->get('dbal_connection')->exec('UPDATE s_core_countries SET allow_shipping = 0 WHERE id = 25');
    }

    /**
     * @return Customer
     */
    public function createResource()
    {
        return new Customer();
    }

    public function testCreateWithNonUniqueEmailShouldThrowException()
    {
        $this->expectException('Shopware\Components\Api\Exception\ValidationException');
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

    public function testCreateShouldBeSuccessful()
    {
        $date = new \DateTime();
        $date->modify('-10 days');
        $firstlogin = $date->format(\DateTime::ISO8601);

        $date->modify('+2 day');
        $lastlogin = $date->format(\DateTime::ISO8601);

        $birthday = \DateTime::createFromFormat('Y-m-d', '1986-12-20')->format(\DateTime::ISO8601);

        $testData = [
            'password' => 'fooobar',
            'email' => Random::getAlphanumericString(5) . 'test@foobar.com',
            'number' => 'testnumber' . uniqid(),
            'firstlogin' => $firstlogin,
            'lastlogin' => $lastlogin,

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

        /** @var \Shopware\Models\Customer\Customer $customer */
        $customer = $this->resource->create($testData);

        static::assertInstanceOf('\Shopware\Models\Customer\Customer', $customer);
        static::assertGreaterThan(0, $customer->getId());

        // Test default values
        static::assertEquals($customer->getShop()->getId(), 1);
        static::assertEquals($customer->getAccountMode(), 0);
        static::assertEquals($customer->getGroup()->getKey(), 'EK');
        static::assertEquals($customer->getActive(), true);

        static::assertEquals($customer->getEmail(), $testData['email']);

        static::assertEquals($customer->getDefaultBillingAddress()->getFirstName(), $testData['billing']['firstname']);
        static::assertEquals($customer->getDefaultBillingAddress()->getFirstname(), $testData['billing']['firstname']);

        static::assertEquals($customer->getDefaultBillingAddress()->getAttribute()->getText1(), $testData['billing']['attribute']['text1']);
        static::assertEquals($customer->getDefaultBillingAddress()->getAttribute()->getText1(), $testData['billing']['attribute']['text1']);

        static::assertEquals($customer->getDefaultShippingAddress()->getFirstName(), $testData['shipping']['firstname']);
        static::assertEquals($customer->getDefaultShippingAddress()->getFirstname(), $testData['shipping']['firstname']);

        static::assertEquals($customer->getDefaultShippingAddress()->getAttribute()->getText1(), $testData['shipping']['attribute']['text1']);
        static::assertEquals($customer->getDefaultShippingAddress()->getAttribute()->getText1(), $testData['shipping']['attribute']['text1']);

        //test additional address lines
        static::assertEquals($customer->getDefaultShippingAddress()->getAdditionalAddressLine1(), $testData['shipping']['additionalAddressLine1']);
        static::assertEquals($customer->getDefaultShippingAddress()->getAdditionalAddressLine2(), $testData['shipping']['additionalAddressLine2']);
        static::assertEquals($customer->getDefaultBillingAddress()->getAdditionalAddressLine1(), $testData['billing']['additionalAddressLine1']);
        static::assertEquals($customer->getDefaultBillingAddress()->getAdditionalAddressLine2(), $testData['billing']['additionalAddressLine2']);

        return $customer->getId();
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetOneShouldBeSuccessful($id)
    {
        $customer = $this->resource->getOne($id);
        static::assertGreaterThan(0, $customer['id']);
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetOneByNumberShouldBeSuccessful($id)
    {
        $this->resource->setResultMode(Resource::HYDRATE_OBJECT);
        $customer = $this->resource->getOne($id);
        $number = $customer->getNumber();

        $customer = $this->resource->getOneByNumber($number);
        static::assertEquals($id, $customer->getId());
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetOneShouldBeAbleToReturnObject($id)
    {
        $this->resource->setResultMode(Resource::HYDRATE_OBJECT);
        $customer = $this->resource->getOne($id);

        static::assertInstanceOf('\Shopware\Models\Customer\Customer', $customer);
        static::assertGreaterThan(0, $customer->getId());
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetListShouldBeSuccessful()
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
    public function testGetListShouldBeAbleToReturnObjects()
    {
        $this->resource->setResultMode(Resource::HYDRATE_OBJECT);
        $result = $this->resource->getList();

        static::assertArrayHasKey('data', $result);
        static::assertArrayHasKey('total', $result);

        static::assertGreaterThanOrEqual(1, $result['total']);
        static::assertGreaterThanOrEqual(1, $result['data']);

        static::assertInstanceOf('\Shopware\Models\Customer\Customer', $result['data'][0]);
    }

    public function testCreateWithInvalidDataShouldThrowValidationException()
    {
        $this->expectException('Shopware\Components\Api\Exception\ValidationException');
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

    public function testCreateWithInvalidDataShouldThrowValidationExceptionWithCorrectToStringMessage()
    {
        $this->expectException('Shopware\Components\Api\Exception\ValidationException');
        $this->expectExceptionMessageRegExp('"salutation: The value you selected is not a valid choice."');
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
    public function testUpdateShouldBeSuccessful($id)
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

        static::assertInstanceOf('\Shopware\Models\Customer\Customer', $customer);
        static::assertEquals($id, $customer->getId());

        static::assertEquals($customer->getEmail(), $testData['email']);
        static::assertEquals($customer->getDefaultBillingAddress()->getFirstName(), $testData['billing']['firstname']);

        //test additional fields
        static::assertEquals($customer->getDefaultBillingAddress()->getAdditionalAddressLine1(), $testData['billing']['additionalAddressLine1']);
        static::assertEquals($customer->getDefaultBillingAddress()->getAdditionalAddressLine2(), $testData['billing']['additionalAddressLine2']);

        static::assertEquals($customer->getDefaultShippingAddress()->getAdditionalAddressLine1(), $testData['shipping']['additionalAddressLine1']);
        static::assertEquals($customer->getDefaultShippingAddress()->getAdditionalAddressLine2(), $testData['shipping']['additionalAddressLine2']);

        return $id;
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testUpdateByNumberShouldBeSuccessful($id)
    {
        $this->resource->setResultMode(Resource::HYDRATE_OBJECT);
        $customer = $this->resource->getOne($id);
        $number = $customer->getNumber();

        $testData = [
            'active' => true,
            'email' => Random::getAlphanumericString(5) . 'update@foobar.com',
            'billing' => [
                'firstname' => 'Max Update',
                'lastname' => 'Mustermann Update',
            ],
        ];

        $customer = $this->resource->updateByNumber($number, $testData);

        static::assertInstanceOf('\Shopware\Models\Customer\Customer', $customer);
        static::assertEquals($id, $customer->getId());

        static::assertEquals($customer->getEmail(), $testData['email']);
        static::assertEquals($customer->getDefaultBillingAddress()->getFirstName(), $testData['billing']['firstname']);

        return $number;
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testUpdateWithInvalidDataShouldThrowValidationException($id)
    {
        $this->expectException('Shopware\Components\Api\Exception\ValidationException');
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

    public function testUpdateWithInvalidIdShouldThrowNotFoundException()
    {
        $this->expectException('Shopware\Components\Api\Exception\NotFoundException');
        $this->resource->update(9999999, []);
    }

    public function testUpdateWithMissingIdShouldThrowParameterMissingException()
    {
        $this->expectException('Shopware\Components\Api\Exception\ParameterMissingException');
        $this->resource->update('', []);
    }

    /**
     * @depends testUpdateShouldBeSuccessful
     */
    public function testDeleteShouldBeSuccessful($id)
    {
        $customer = $this->resource->delete($id);

        static::assertInstanceOf('\Shopware\Models\Customer\Customer', $customer);
        static::assertEquals(null, $customer->getId());
    }

    public function testDeleteWithInvalidIdShouldThrowNotFoundException()
    {
        $this->expectException('Shopware\Components\Api\Exception\NotFoundException');
        $this->resource->delete(9999999);
    }

    public function testDeleteWithMissingIdShouldThrowParameterMissingException()
    {
        $this->expectException('Shopware\Components\Api\Exception\ParameterMissingException');
        $this->resource->delete('');
    }

    /**
     * @return int
     */
    public function testPostCustomersWithDebitShouldCreatePaymentData()
    {
        $date = new \DateTime();
        $date->modify('-10 days');
        $firstlogin = $date->format(\DateTime::ISO8601);

        $date->modify('+2 day');
        $lastlogin = $date->format(\DateTime::ISO8601);

        $birthday = \DateTime::createFromFormat('Y-m-d', '1986-12-20')->format(\DateTime::ISO8601);

        $requestData = [
            'password' => 'fooobar',
            'active' => true,
            'email' => Random::getAlphanumericString(5) . 'test1@foobar.com',

            'firstlogin' => $firstlogin,
            'lastlogin' => $lastlogin,

            'salutation' => 'mr',
            'firstname' => 'Max',
            'lastname' => 'Mustermann',
            'birthday' => $birthday,

            'billing' => [
                'salutation' => 'mr',
                'firstname' => 'Max',
                'lastname' => 'Mustermann',
                'street' => 'Musterstraße 123',
                'zipcode' => 12345,
                'city' => 'Musterhausen',
                'country' => '2',
            ],

            'shipping' => [
                'salutation' => 'Mr',
                'company' => 'Widgets Inc.',
                'firstname' => 'Max',
                'lastname' => 'Mustermann',
                'street' => 'Musterstraße 123',
                'zipcode' => 12345,
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
        $identifier = $customer->getId();

        $this->resource->getManager()->clear();
        $customer = Shopware()->Models()->getRepository('Shopware\Models\Customer\Customer')->find($identifier);

        $paymentData = array_shift($customer->getPaymentData()->toArray());

        static::assertNotNull($paymentData);
        static::assertEquals('Max Mustermann', $paymentData->getAccountHolder());
        static::assertEquals('Fake Account', $paymentData->getAccountNumber());
        static::assertEquals('Fake Bank', $paymentData->getBankName());
        static::assertEquals('55555555', $paymentData->getBankCode());

        $this->testDeleteShouldBeSuccessful($identifier);
    }

    /**
     * @return int
     */
    public function testPostCustomersWithDebitPaymentDataShouldCreateDebitData()
    {
        $date = new \DateTime();
        $date->modify('-10 days');
        $firstlogin = $date->format(\DateTime::ISO8601);

        $date->modify('+2 day');
        $lastlogin = $date->format(\DateTime::ISO8601);

        $birthday = \DateTime::createFromFormat('Y-m-d', '1986-12-20')->format(\DateTime::ISO8601);

        $requestData = [
            'password' => 'fooobar',
            'active' => true,
            'email' => Random::getAlphanumericString(5) . 'test2@foobar.com',

            'firstlogin' => $firstlogin,
            'lastlogin' => $lastlogin,

            'salutation' => 'mr',
            'firstname' => 'Max',
            'lastname' => 'Mustermann',
            'birthday' => $birthday,

            'billing' => [
                'salutation' => 'mr',
                'firstname' => 'Max',
                'lastname' => 'Mustermann',
                'street' => 'Musterstraße 123',
                'zipcode' => 12345,
                'city' => 'Musterhausen',
                'country' => '2',
            ],

            'shipping' => [
                'salutation' => 'Mr',
                'company' => 'Widgets Inc.',
                'firstname' => 'Max',
                'lastname' => 'Mustermann',
                'street' => 'Musterstraße 123',
                'zipcode' => 12345,
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
        $customer = Shopware()->Models()->getRepository('Shopware\Models\Customer\Customer')->find($identifier);

        $paymentData = array_shift($customer->getPaymentData()->toArray());

        static::assertNotNull($paymentData);
        static::assertEquals('Max Mustermann', $paymentData->getAccountHolder());
        static::assertEquals('Fake Account', $paymentData->getAccountNumber());
        static::assertEquals('Fake Bank', $paymentData->getBankName());
        static::assertEquals('55555555', $paymentData->getBankCode());

        $this->testDeleteShouldBeSuccessful($identifier);
    }

    public function testCreateWithDifferentCustomerGroup()
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
        static::assertEquals('H', $customer->getGroup()->getKey());
    }

    public function testCreateCustomerWithDefaultShopCustomerGroup()
    {
        $context = Shopware()->Container()->get('shopware_storefront.context_service')->createShopContext(1);
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
        static::assertEquals($context->getShop()->getCustomerGroup()->getKey(), $customer->getGroup()->getKey());
    }

    /**
     * @group failing
     */
    public function testCreateCustomerCreatesCustomerAttribute()
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

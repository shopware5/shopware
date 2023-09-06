<?php

declare(strict_types=1);
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Tests\Functional\Controllers\Backend;

use Enlight_Components_Test_Controller_TestCase;
use Enlight_Controller_Response_Response;
use Enlight_Controller_Response_ResponseTestCase;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Country\Country;
use Shopware\Models\Customer\Address;
use Shopware\Models\Customer\Customer;
use Shopware\Models\Customer\Group;
use Shopware\Models\Customer\PaymentData;
use Shopware\Models\Customer\Repository;
use Shopware\Models\Payment\Payment;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class CustomerTest extends Enlight_Components_Test_Controller_TestCase
{
    use DatabaseTransactionBehaviour;

    private Repository $repository;

    private ModelManager $manager;

    /**
     * Standard set up for every test - just disable auth
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->manager = Shopware()->Models();
        $this->repository = Shopware()->Models()->getRepository(Customer::class);

        // Disable auth and acl
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        Shopware()->Plugins()->Backend()->Auth()->setNoAcl();
    }

    public function testSensitiveDataIsNotSend(): void
    {
        $customer = $this->createDummyCustomer();

        $params = [
            'customerID' => $customer->getId(),
        ];
        $this->Request()->setMethod('POST')->setPost($params);
        $this->dispatch('/backend/Customer/getDetail');

        $body = $this->View()->getAssign();
        static::assertTrue($body['success']);
        static::assertArrayNotHasKey('hashPassword', $body['data']);
        static::assertArrayNotHasKey('sessionId', $body['data']);
        static::assertEquals('test@phpunit.org', $body['data']['email']);
    }

    /**
     * Test saveAction controller action - change payment mean
     *
     * Get a random customer. Change payment method to debit
     */
    public function testChangeCustomerPaymentMean(): void
    {
        $customer = $this->createDummyCustomer();

        static::assertEquals(0, $customer->getPaymentId());

        $debit = $this->manager
            ->getRepository(Payment::class)
            ->findOneBy(['name' => 'debit']);
        static::assertInstanceOf(Payment::class, $debit);

        static::assertNotNull($customer->getChanged());
        $params = [
            'id' => $customer->getId(),
            'paymentId' => $debit->getId(),
            'changed' => $customer->getChanged()->format('c'),
        ];
        $this->Request()->setMethod('POST')->setPost($params);
        $this->dispatch('/backend/Customer/save');
        $jsonBody = $this->View()->getAssign();

        static::assertTrue($this->View()->getAssign('success'));
        static::assertEquals($debit->getId(), $jsonBody['data']['paymentId']);

        $this->manager->refresh($customer);
        static::assertEquals($debit->getId(), $customer->getPaymentId());

        $this->manager->remove($customer);
        $this->manager->flush();
    }

    /**
     * Test saveAction controller action - new customer with debit payment data
     */
    public function testAddCustomerPaymentDataWithDebit(): int
    {
        $debit = $this->manager
            ->getRepository(Payment::class)
            ->findOneBy(['name' => 'debit']);
        static::assertInstanceOf(Payment::class, $debit);

        $params = [
            'paymentId' => $debit->getId(),
            'email' => 'test@shopware.de',
            'newPassword' => '222',
            'paymentData' => [[
                'accountHolder' => 'Account Holder Name',
                'accountNumber' => '1234567890',
                'bankCode' => '2345678901',
                'bankName' => 'Bank name',
                'bic' => '',
                'iban' => '',
                'useBillingData' => false,
            ]],
        ];
        $this->Request()->setMethod('POST')->setPost($params);
        $this->dispatch('/backend/Customer/save');
        $jsonBody = $this->View()->getAssign();

        static::assertTrue($this->View()->getAssign('success'));
        static::assertEquals($debit->getId(), $jsonBody['data']['paymentId']);

        $customer = $this->repository->find($this->View()->getAssign('data')['id']);
        static::assertInstanceOf(Customer::class, $customer);

        static::assertEquals($debit->getId(), $customer->getPaymentId());
        static::assertCount(1, $customer->getPaymentData()->toArray());

        $paymentDataArray = $customer->getPaymentData()->toArray();
        $paymentData = array_shift($paymentDataArray);
        static::assertInstanceOf(PaymentData::class, $paymentData);
        static::assertEquals('Account Holder Name', $paymentData->getAccountHolder());
        static::assertEquals('1234567890', $paymentData->getAccountNumber());
        static::assertEquals('2345678901', $paymentData->getBankCode());
        static::assertEquals('Bank name', $paymentData->getBankName());
        static::assertEmpty($paymentData->getBic());
        static::assertEmpty($paymentData->getIban());
        static::assertFalse($paymentData->getUseBillingData());

        return $customer->getId();
    }

    /**
     * Test create customer with address
     */
    public function testAddCustomerWithAddress(): void
    {
        $debit = $this->manager
            ->getRepository(Payment::class)
            ->findOneBy(['name' => 'debit']);
        static::assertInstanceOf(Payment::class, $debit);

        $params = [
            'paymentId' => $debit->getId(),
            'email' => 'debit@shopware.de',
            'newPassword' => '222',
        ];
        $this->Request()->setMethod('POST')->setPost($params);
        $this->dispatch('/backend/Customer/save');
        $jsonBody = $this->View()->getAssign();

        static::assertTrue($this->View()->getAssign('success'));
        static::assertEquals($debit->getId(), $jsonBody['data']['paymentId']);

        $params = [
            'id' => null,
            'defaultAddress' => '',
            'setDefaultBillingAddress' => true,
            'setDefaultShippingAddress' => true,
            'user_id' => $this->View()->getAssign('data')['id'],
            'company' => 'company',
            'department' => 'department',
            'vatId' => 'vatId',
            'salutation' => 'mr',
            'salutationSnippet' => '',
            'title' => 'title',
            'firstname' => 'firstname',
            'lastname' => 'lastname',
            'street' => 'street',
            'zipcode' => 'zipcode',
            'city' => 'city',
            'additionalAddressLine1' => 'additionalAddressLine1',
            'additionalAddressLine2' => 'additionalAddressLine2',
            'countryId' => 3,
            'stateId' => null,
            'phone' => '',
            'customer' => [],
            'country' => [],
            'state' => [],
        ];

        $this->reset();

        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        Shopware()->Plugins()->Backend()->Auth()->setNoAcl();

        $this->Request()->setMethod('POST')->setPost($params);

        $this->dispatch('/backend/Address/create');

        $customer = $this->repository->find($params['user_id']);
        static::assertInstanceOf(Customer::class, $customer);

        $billingAddress = $customer->getDefaultBillingAddress();
        static::assertInstanceOf(Address::class, $billingAddress);
        static::assertEquals('firstname', $billingAddress->getFirstname());
        static::assertEquals('lastname', $billingAddress->getLastname());
        static::assertEquals('department', $billingAddress->getDepartment());
        static::assertEquals('vatId', $billingAddress->getVatId());
        static::assertEquals('title', $billingAddress->getTitle());
        static::assertEquals('zipcode', $billingAddress->getZipcode());
        static::assertEquals('city', $billingAddress->getCity());
        static::assertEquals('street', $billingAddress->getStreet());
        static::assertEquals('additionalAddressLine1', $billingAddress->getAdditionalAddressLine1());
        static::assertEquals('additionalAddressLine2', $billingAddress->getAdditionalAddressLine2());

        $shippingAddress = $customer->getDefaultShippingAddress();
        static::assertInstanceOf(Address::class, $shippingAddress);
        static::assertEquals('firstname', $shippingAddress->getFirstname());
        static::assertEquals('lastname', $shippingAddress->getLastname());
        static::assertEquals('department', $shippingAddress->getDepartment());
        static::assertEquals('vatId', $shippingAddress->getVatId());
        static::assertEquals('title', $shippingAddress->getTitle());
        static::assertEquals('zipcode', $shippingAddress->getZipcode());
        static::assertEquals('city', $shippingAddress->getCity());
        static::assertEquals('street', $shippingAddress->getStreet());
        static::assertEquals('additionalAddressLine1', $shippingAddress->getAdditionalAddressLine1());
        static::assertEquals('additionalAddressLine2', $shippingAddress->getAdditionalAddressLine2());
    }

    /**
     * Test saveAction controller action - Update an existing customer
     */
    public function testUpdateCustomerPaymentDataWithSepa(): void
    {
        $customerId = $this->testAddCustomerPaymentDataWithDebit();
        $customer = $this->repository->find($customerId);
        static::assertInstanceOf(Customer::class, $customer);

        $sepa = $this->manager
            ->getRepository(Payment::class)
            ->findOneBy(['name' => 'sepa']);
        static::assertInstanceOf(Payment::class, $sepa);

        $debit = $this->manager
            ->getRepository(Payment::class)
            ->findOneBy(['name' => 'debit']);
        static::assertInstanceOf(Payment::class, $debit);

        static::assertEquals($debit->getId(), $customer->getPaymentId());
        static::assertCount(1, $customer->getPaymentData()->toArray());
        static::assertNotNull($customer->getChanged());

        $params = [
            'id' => $customer->getId(),
            'paymentId' => $sepa->getId(),
            'paymentData' => [[
                'accountHolder' => '',
                'accountNumber' => '',
                'bankCode' => '',
                'bankName' => 'European bank name',
                'bic' => '123bic312',
                'iban' => '456iban654',
                'useBillingData' => true,
            ]],
            'changed' => $customer->getChanged()->format('c'),
        ];
        $this->Request()->setMethod('POST')->setPost($params);
        $this->dispatch('/backend/Customer/save');
        $jsonBody = $this->View()->getAssign();

        static::assertTrue($this->View()->getAssign('success'));
        static::assertEquals($sepa->getId(), $jsonBody['data']['paymentId']);

        $this->manager->refresh($customer);

        static::assertEquals($sepa->getId(), $customer->getPaymentId());
        $paymentDataArray = $customer->getPaymentData()->toArray();
        static::assertCount(2, $paymentDataArray);

        // Old debit payment data is still there, it's just not used currently
        $paymentData = array_shift($paymentDataArray);
        static::assertInstanceOf(PaymentData::class, $paymentData);
        static::assertEquals('Account Holder Name', $paymentData->getAccountHolder());
        static::assertEquals('1234567890', $paymentData->getAccountNumber());
        static::assertEquals('2345678901', $paymentData->getBankCode());
        static::assertEquals('Bank name', $paymentData->getBankName());
        static::assertEmpty($paymentData->getBic());
        static::assertEmpty($paymentData->getIban());
        static::assertFalse($paymentData->getUseBillingData());

        // New SEPA data
        $paymentData = array_shift($paymentDataArray);
        static::assertInstanceOf(PaymentData::class, $paymentData);
        static::assertEmpty($paymentData->getAccountHolder());
        static::assertEmpty($paymentData->getAccountNumber());
        static::assertEmpty($paymentData->getBankCode());
        static::assertEquals('European bank name', $paymentData->getBankName());
        static::assertEquals('123bic312', $paymentData->getBic());
        static::assertEquals('456iban654', $paymentData->getIban());
        static::assertTrue($paymentData->getUseBillingData());

        $this->manager->remove($customer);
        $this->manager->flush();
    }

    /**
     * Test that performOrderAction() sets the correct cookie settings
     */
    public function testPerformOrderAction(): void
    {
        $customer = $this->createDummyCustomer();

        $this->Request()->setParams(['id' => $customer->getId()]);

        $response = $this->dispatch('backend/Customer/performOrder');
        static::assertInstanceOf(Enlight_Controller_Response_ResponseTestCase::class, $response);

        $headerLocation = $response->getHeader('Location');
        $this->reset();
        static::assertNotEmpty($headerLocation);
        $newLocation = explode('/backend/', $headerLocation);
        $response = $this->dispatch('backend/' . $newLocation[1]);

        $cookie = $this->getCookie($response, 'session-1');
        static::assertIsArray($cookie);
        static::assertNotEmpty($cookie);
        static::assertEquals(0, $cookie['expire']);
    }

    /**
     * Tests whether a customer cannot be overwritten by a save request that bases on outdated data. (The customer in the
     * database is newer than that one the request body is based on.)
     */
    public function testSaveCustomerOverwriteProtection(): void
    {
        // Prepare data for the test
        $customer = $this->createDummyCustomer();
        static::assertNotNull($customer->getChanged());

        // Prepare post data for request
        $postData = [
            'id' => $customer->getId(),
            'changed' => $customer->getChanged()->format('c'),
        ];

        // Try to change the entity with the correct timestamp. This should work
        $this->Request()
            ->setMethod('POST')
            ->setPost($postData);
        $this->dispatch('backend/Customer/save');
        static::assertTrue($this->View()->getAssign('success'));

        // Now use an outdated timestamp. The controller should detect this and fail.
        $postData['changed'] = '2008-08-07 18:11:31';
        $this->Request()
            ->setMethod('POST')
            ->setPost($postData);
        $this->dispatch('backend/Customer/save');
        static::assertFalse($this->View()->getAssign('success'));
    }

    /**
     * SW-6667 Tests if the customer has an id to check if lazy loading was fetching the data
     */
    public function testCustomerId(): void
    {
        $dummy = $this->createDummyCustomer();

        $customer = Shopware()->Models()->find(Customer::class, $dummy->getId());

        static::assertInstanceOf(Customer::class, $customer);
        $customerGroup = $customer->getGroup();
        static::assertInstanceOf(Group::class, $customerGroup);
        static::assertSame(1, $customerGroup->getId());
    }

    /**
     * @return array<string, mixed>|null
     */
    private function getCookie(Enlight_Controller_Response_Response $response, string $name): ?array
    {
        foreach ($response->getCookies() as $cookie) {
            if ($cookie['name'] === $name) {
                return $cookie;
            }
        }

        return null;
    }

    private function createDummyCustomer(): Customer
    {
        $dummyData = new Customer();
        $dummyData->setEmail('test@phpunit.org');
        $customerGroup = $this->manager->find(Group::class, 1);
        static::assertInstanceOf(Group::class, $customerGroup);
        $dummyData->setGroup($customerGroup);
        $this->manager->persist($dummyData);
        $this->manager->flush();

        $address = new Address();
        $address->fromArray([
            'firstname' => 'test',
            'lastname' => 'test',
            'zipcode' => 'test',
            'city' => 'test',
            'customer' => $dummyData,
            'country' => $this->manager->find(Country::class, 2),
        ]);
        $this->manager->persist($address);
        $this->manager->flush();

        $dummyData->setDefaultBillingAddress($address);

        $this->manager->persist($dummyData);
        $this->manager->flush();

        return $dummyData;
    }
}

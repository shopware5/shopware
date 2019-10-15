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

namespace Shopware\Tests\Functional\Controllers\Backend;

class CustomerTest extends \Enlight_Components_Test_Controller_TestCase
{
    /** @var \Shopware\Models\Customer\Customer $repository */
    protected $repository;

    /** @var \Shopware\Components\Model\ModelManager */
    private $manager;

    /**
     * Standard set up for every test - just disable auth
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->manager = Shopware()->Models();
        $this->repository = Shopware()->Models()->getRepository(\Shopware\Models\Customer\Customer::class);

        // Disable auth and acl
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        Shopware()->Plugins()->Backend()->Auth()->setNoAcl();
    }

    /**
     * Test saveAction controller action - change payment mean
     *
     * Get a random customer. Change payment method to debit
     */
    public function testChangeCustomerPaymentMean()
    {
        $customer = $this->createDummyCustomer();

        static::assertEquals(0, $customer->getPaymentId());

        $debit = $this->manager
            ->getRepository(\Shopware\Models\Payment\Payment::class)
            ->findOneBy(['name' => 'debit']);

        $params = [
            'id' => $customer->getId(),
            'paymentId' => $debit->getId(),
            'changed' => $customer->getChanged()->format('c'),
        ];
        $this->Request()->setMethod('POST')->setPost($params);
        $this->dispatch('/backend/Customer/save');
        $jsonBody = $this->View()->getAssign();

        static::assertTrue($this->View()->success);
        static::assertEquals($debit->getId(), $jsonBody['data']['paymentId']);

        $this->manager->refresh($customer);
        static::assertEquals($debit->getId(), $customer->getPaymentId());

        $this->manager->remove($customer);
        $this->manager->flush();
    }

    /**
     * Test saveAction controller action - new customer with debit payment data
     */
    public function testAddCustomerPaymentDataWithDebit()
    {
        $debit = $this->manager
            ->getRepository(\Shopware\Models\Payment\Payment::class)
            ->findOneBy(['name' => 'debit']);

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

        static::assertTrue($this->View()->success);
        static::assertEquals($debit->getId(), $jsonBody['data']['paymentId']);

        $dummyData = $this->repository->find($this->View()->data['id']);

        static::assertEquals($debit->getId(), $dummyData->getPaymentId());
        static::assertCount(1, $dummyData->getPaymentData()->toArray());

        /** @var \Shopware\Models\Customer\PaymentData $paymentData */
        $paymentData = array_shift($dummyData->getPaymentData()->toArray());
        static::assertInstanceOf(\Shopware\Models\Customer\PaymentData::class, $paymentData);
        static::assertEquals('Account Holder Name', $paymentData->getAccountHolder());
        static::assertEquals('1234567890', $paymentData->getAccountNumber());
        static::assertEquals('2345678901', $paymentData->getBankCode());
        static::assertEquals('Bank name', $paymentData->getBankName());
        static::assertEmpty($paymentData->getBic());
        static::assertEmpty($paymentData->getIban());
        static::assertFalse($paymentData->getUseBillingData());

        return $dummyData->getId();
    }

    /**
     * Test create customer with address
     */
    public function testAddCustomerWithAddress()
    {
        $debit = $this->manager
            ->getRepository(\Shopware\Models\Payment\Payment::class)
            ->findOneBy(['name' => 'debit']);

        $params = [
            'paymentId' => $debit->getId(),
            'email' => 'debit@shopware.de',
            'newPassword' => '222',
        ];
        $this->Request()->setMethod('POST')->setPost($params);
        $this->dispatch('/backend/Customer/save');
        $jsonBody = $this->View()->getAssign();

        static::assertTrue($this->View()->success);
        static::assertEquals($debit->getId(), $jsonBody['data']['paymentId']);

        $params = [
            'id' => null,
            'defaultAddress' => '',
            'setDefaultBillingAddress' => true,
            'setDefaultShippingAddress' => true,
            'user_id' => $this->View()->data['id'],
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

        /** @var \Shopware\Models\Customer\Customer $dummyData */
        $dummyData = $this->repository->find($params['user_id']);

        static::assertEquals('firstname', $dummyData->getDefaultBillingAddress()->getFirstname());
        static::assertEquals('lastname', $dummyData->getDefaultBillingAddress()->getLastname());
        static::assertEquals('department', $dummyData->getDefaultBillingAddress()->getDepartment());
        static::assertEquals('vatId', $dummyData->getDefaultBillingAddress()->getVatId());
        static::assertEquals('title', $dummyData->getDefaultBillingAddress()->getTitle());
        static::assertEquals('zipcode', $dummyData->getDefaultBillingAddress()->getZipcode());
        static::assertEquals('city', $dummyData->getDefaultBillingAddress()->getCity());
        static::assertEquals('street', $dummyData->getDefaultBillingAddress()->getStreet());
        static::assertEquals('additionalAddressLine1', $dummyData->getDefaultBillingAddress()->getAdditionalAddressLine1());
        static::assertEquals('additionalAddressLine2', $dummyData->getDefaultBillingAddress()->getAdditionalAddressLine2());

        static::assertEquals('firstname', $dummyData->getDefaultShippingAddress()->getFirstname());
        static::assertEquals('lastname', $dummyData->getDefaultShippingAddress()->getLastname());
        static::assertEquals('department', $dummyData->getDefaultShippingAddress()->getDepartment());
        static::assertEquals('vatId', $dummyData->getDefaultShippingAddress()->getVatId());
        static::assertEquals('title', $dummyData->getDefaultShippingAddress()->getTitle());
        static::assertEquals('zipcode', $dummyData->getDefaultShippingAddress()->getZipcode());
        static::assertEquals('city', $dummyData->getDefaultShippingAddress()->getCity());
        static::assertEquals('street', $dummyData->getDefaultShippingAddress()->getStreet());
        static::assertEquals('additionalAddressLine1', $dummyData->getDefaultShippingAddress()->getAdditionalAddressLine1());
        static::assertEquals('additionalAddressLine2', $dummyData->getDefaultShippingAddress()->getAdditionalAddressLine2());
    }

    /**
     * Test saveAction controller action - Update an existing customer
     *
     * @depends testAddCustomerPaymentDataWithDebit
     */
    public function testUpdateCustomerPaymentDataWithSepa($dummyDataId)
    {
        /** @var \Shopware\Models\Customer\Customer $dummyData */
        $dummyData = $this->repository->find($dummyDataId);
        $sepa = $this->manager
            ->getRepository(\Shopware\Models\Payment\Payment::class)
            ->findOneBy(['name' => 'sepa']);
        $debit = $this->manager
            ->getRepository(\Shopware\Models\Payment\Payment::class)
            ->findOneBy(['name' => 'debit']);

        static::assertEquals($debit->getId(), $dummyData->getPaymentId());
        static::assertCount(1, $dummyData->getPaymentData()->toArray());

        $params = [
            'id' => $dummyData->getId(),
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
            'changed' => $dummyData->getChanged()->format('c'),
        ];
        $this->Request()->setMethod('POST')->setPost($params);
        $this->dispatch('/backend/Customer/save');
        $jsonBody = $this->View()->getAssign();

        static::assertTrue($this->View()->success);
        static::assertEquals($sepa->getId(), $jsonBody['data']['paymentId']);

        $this->manager->refresh($dummyData);

        static::assertEquals($sepa->getId(), $dummyData->getPaymentId());
        $paymentDataArray = $dummyData->getPaymentData()->toArray();
        static::assertCount(2, $paymentDataArray);

        // Old debit payment data is still there, it's just not used currently
        /** @var \Shopware\Models\Customer\PaymentData $paymentData */
        $paymentData = array_shift($paymentDataArray);
        static::assertInstanceOf(\Shopware\Models\Customer\PaymentData::class, $paymentData);
        static::assertEquals('Account Holder Name', $paymentData->getAccountHolder());
        static::assertEquals('1234567890', $paymentData->getAccountNumber());
        static::assertEquals('2345678901', $paymentData->getBankCode());
        static::assertEquals('Bank name', $paymentData->getBankName());
        static::assertEmpty($paymentData->getBic());
        static::assertEmpty($paymentData->getIban());
        static::assertFalse($paymentData->getUseBillingData());

        // New SEPA data
        /** @var \Shopware\Models\Customer\PaymentData $paymentData */
        $paymentData = array_shift($paymentDataArray);
        static::assertInstanceOf(\Shopware\Models\Customer\PaymentData::class, $paymentData);
        static::assertEmpty($paymentData->getAccountHolder());
        static::assertEmpty($paymentData->getAccountNumber());
        static::assertEmpty($paymentData->getBankCode());
        static::assertEquals('European bank name', $paymentData->getBankName());
        static::assertEquals('123bic312', $paymentData->getBic());
        static::assertEquals('456iban654', $paymentData->getIban());
        static::assertTrue($paymentData->getUseBillingData());

        $this->manager->remove($dummyData);
        $this->manager->flush();
    }

    /**
     * Test that performOrderAction() sets the correct cookie settings
     */
    public function testPerformOrderAction()
    {
        $customer = $this->createDummyCustomer();

        $this->Request()->setParams(['id' => $customer->getId()]);

        /** @var \Enlight_Controller_Response_ResponseTestCase $response */
        $response = $this->dispatch('backend/Customer/performOrder');

        $headerLocation = $response->getHeader('Location');
        $this->reset();
        static::assertNotEmpty($headerLocation);
        $newLocation = explode('/backend/', $headerLocation);
        $response = $this->dispatch('backend/' . $newLocation[1]);

        $cookie = $this->getCookie($response, 'session-1');
        static::assertNotEmpty($cookie);
        static::assertEquals(0, $cookie['expire']);
    }

    /**
     * Tests whether a customer cannot be overwritten by a save request that bases on outdated data. (The customer in the
     * database is newer than that one the request body is based on.)
     */
    public function testSaveCustomerOverwriteProtection()
    {
        // Prepare data for the test
        $customer = $this->createDummyCustomer();

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
        static::assertTrue($this->View()->success);

        // Now use an outdated timestamp. The controller should detect this and fail.
        $postData['changed'] = '2008-08-07 18:11:31';
        $this->Request()
            ->setMethod('POST')
            ->setPost($postData);
        $this->dispatch('backend/Customer/save');
        static::assertFalse($this->View()->success);
    }

    /**
     * SW-6667 Tests if the customer has an id to check if lazy loading was fetching the data
     */
    public function testCustomerId()
    {
        $dummy = $this->createDummyCustomer();

        $customer = Shopware()->Models()->find(\Shopware\Models\Customer\Customer::class, $dummy->getId());

        static::assertInstanceOf(\Shopware\Models\Customer\Customer::class, $customer);
        static::assertEquals('1', $customer->getGroup()->getId());
    }

    private function getCookie(\Enlight_Controller_Response_Response $response, $name)
    {
        $cookies = $response->getCookies();
        foreach ($cookies as $cookie) {
            if ($cookie['name'] === $name) {
                return $cookie;
            }
        }

        return null;
    }

    /**
     * @return \Shopware\Models\Customer\Customer
     */
    private function createDummyCustomer()
    {
        $dummyData = new \Shopware\Models\Customer\Customer();
        $dummyData->setEmail('test@phpunit.org');
        $dummyData->setGroup($this->manager->find(\Shopware\Models\Customer\Group::class, 1));
        $this->manager->persist($dummyData);
        $this->manager->flush();

        $address = new \Shopware\Models\Customer\Address();
        $address->fromArray([
            'firstname' => 'test',
            'lastname' => 'test',
            'zipcode' => 'test',
            'city' => 'test',
            'customer' => $dummyData,
            'country' => $this->manager->find(\Shopware\Models\Country\Country::class, 2),
        ]);
        $this->manager->persist($address);
        $this->manager->flush();

        $dummyData->setDefaultBillingAddress($address);

        $this->manager->persist($dummyData);
        $this->manager->flush();

        return $dummyData;
    }
}

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

namespace Shopware\Tests\Functional\Api;

use DateTime;
use DateTimeInterface;
use Doctrine\DBAL\Connection;
use Enlight_Controller_Response_ResponseTestCase;
use Shopware\Components\Api\Resource\Customer as CustomerResource;
use Shopware\Models\Customer\Customer;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;
use Symfony\Component\HttpFoundation\Response;

class CustomerTest extends AbstractApiTestCase
{
    use ContainerTrait;
    use DatabaseTransactionBehaviour;

    public function testRequestWithoutAuthenticationShouldReturnError(): void
    {
        $this->client->request('GET', '/api/customers/');
        $response = $this->client->getResponse();

        static::assertSame('application/json', $response->headers->get('Content-Type'));
        static::assertSame(401, $response->getStatusCode());

        $result = $response->getContent();
        static::assertIsString($result);
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);

        static::assertArrayHasKey('message', $result);
    }

    public function testGetCustomersWithInvalidIdShouldReturnMessage(): void
    {
        $id = 99999999;

        $this->authenticatedApiRequest('GET', '/api/customers/' . $id);
        $response = $this->client->getResponse();

        static::assertSame('application/json', $response->headers->get('Content-Type'));
        static::assertSame(404, $response->getStatusCode());

        $result = $response->getContent();
        static::assertIsString($result);
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);

        static::assertArrayHasKey('message', $result);
    }

    public function testPostCustomersShouldBeSuccessful(): void
    {
        $requestData = $this->getCustomerRequestData();

        $this->authenticatedApiRequest('POST', '/api/customers/', [], $requestData);
        $response = $this->client->getResponse();

        static::assertSame('application/json', $response->headers->get('Content-Type'));
        static::assertSame(201, $response->getStatusCode());
        static::assertArrayHasKey('location', $response->headers->all());

        $result = $response->getContent();
        static::assertIsString($result);
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertTrue($result['success']);

        $location = $response->headers->get('location');
        static::assertIsString($location);
        $locationPars = explode('/', $location);
        $identifier = (int) array_pop($locationPars);

        static::assertGreaterThan(0, $identifier);
    }

    public function testPostCustomersWithDebitShouldCreatePaymentData(): void
    {
        $customerId = $this->createNewCustomerWithResource();

        $customer = $this->getContainer()->get('models')->getRepository(Customer::class)->find($customerId);
        static::assertInstanceOf(Customer::class, $customer);
        $payments = $customer->getPaymentData()->toArray();
        $paymentData = array_shift($payments);

        static::assertNotNull($paymentData);
        static::assertSame('Max Mustermann', $paymentData->getAccountHolder());
        static::assertSame('Fake Account', $paymentData->getAccountNumber());
        static::assertSame('Fake Bank', $paymentData->getBankName());
        static::assertSame('55555555', $paymentData->getBankCode());
    }

    public function testPostCustomersWithDebitPaymentDataShouldCreateDebitData(): void
    {
        $customerId = $this->createNewCustomerWithResource(null, [
            'paymentData' => [
                [
                    'paymentMeanId' => 2,
                    'accountNumber' => 'Fake Account',
                    'bankCode' => '55555555',
                    'bankName' => 'Fake Bank',
                    'accountHolder' => 'Max Mustermann',
                ],
            ],
        ]);

        $customer = $this->getContainer()->get('models')->getRepository(Customer::class)->find($customerId);
        static::assertInstanceOf(Customer::class, $customer);
        $payments = $customer->getPaymentData()->toArray();
        $paymentData = array_shift($payments);

        static::assertNotNull($paymentData);
        static::assertSame('Max Mustermann', $paymentData->getAccountHolder());
        static::assertSame('Fake Account', $paymentData->getAccountNumber());
        static::assertSame('Fake Bank', $paymentData->getBankName());
        static::assertSame('55555555', $paymentData->getBankCode());
    }

    public function testPostCustomersWithInvalidDataShouldReturnError(): void
    {
        $requestData = [
            'active' => true,
            'email' => 'invalid',
            'billing' => [
                'firstName' => 'Max',
                'lastName' => 'Mustermann',
            ],
        ];

        $this->authenticatedApiRequest('POST', '/api/customers/', [], $requestData);
        $response = $this->client->getResponse();

        static::assertSame('application/json', $response->headers->get('Content-Type'));
        static::assertSame(400, $response->getStatusCode());

        $result = $response->getContent();
        static::assertIsString($result);
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);
        static::assertArrayHasKey('message', $result);
    }

    public function testGetCustomersWithIdShouldBeSuccessful(): void
    {
        $customerId = $this->createNewCustomerWithResource();

        $this->authenticatedApiRequest('GET', '/api/customers/' . $customerId);
        $response = $this->client->getResponse();

        static::assertSame('application/json', $response->headers->get('Content-Type'));
        static::assertSame(200, $response->getStatusCode());

        $result = $response->getContent();
        static::assertIsString($result);
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertTrue($result['success']);

        static::assertArrayHasKey('data', $result);

        $data = $result['data'];
        static::assertIsArray($data);
        static::assertArrayHasKey('id', $data);
        static::assertArrayHasKey('active', $data);
        static::assertArrayHasKey('paymentData', $data);

        static::assertStringContainsString('test@foobar.com', $data['email']);

        $paymentInfo = array_shift($data['paymentData']);

        static::assertSame('Max Mustermann', $paymentInfo['accountHolder']);
        static::assertSame('55555555', $paymentInfo['bankCode']);
        static::assertSame('Fake Bank', $paymentInfo['bankName']);
        static::assertSame('Fake Account', $paymentInfo['accountNumber']);
    }

    public function testPutBatchCustomersShouldFail(): void
    {
        $requestData = [
            'active' => true,
            'email' => 'test@foobar.com',
        ];

        $this->authenticatedApiRequest('PUT', '/api/customers/', [], $requestData);
        $response = $this->client->getResponse();

        static::assertSame('application/json', $response->headers->get('Content-Type'));
        static::assertSame(405, $response->getStatusCode());

        $result = $response->getContent();
        static::assertIsString($result);
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);
        static::assertSame('This resource has no support for batch operations', $result['message']);
    }

    public function testPutCustomersWithInvalidDataShouldReturnError(): void
    {
        $customerId = $this->createNewCustomerWithResource();

        $requestData = [
            'active' => true,
            'email' => 'invalid',
        ];

        $this->authenticatedApiRequest('PUT', '/api/customers/' . $customerId, [], $requestData);
        $response = $this->client->getResponse();

        static::assertSame('application/json', $response->headers->get('Content-Type'));
        static::assertSame(400, $response->getStatusCode());

        $result = $response->getContent();
        static::assertIsString($result);
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);

        static::assertArrayHasKey('message', $result);
    }

    public function testPutCustomersShouldBeSuccessful(): int
    {
        $customerId = $this->createNewCustomerWithResource();

        $customer = $this->getContainer()->get('models')->getRepository(Customer::class)->find($customerId);
        static::assertInstanceOf(Customer::class, $customer);

        $requestData = [
            'active' => true,
            'email' => $customer->getEmail(),
        ];

        $this->authenticatedApiRequest('PUT', '/api/customers/' . $customerId, [], $requestData);
        $response = $this->client->getResponse();

        static::assertSame(200, $response->getStatusCode());
        static::assertSame('application/json', $response->headers->get('Content-Type'));
        static::assertNull(
            $response->headers->get('location'),
            'There should be no location header set.'
        );

        $result = $response->getContent();
        static::assertIsString($result);
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertTrue($result['success']);

        return $customerId;
    }

    public function testDeleteCustomersShouldBeSuccessful(): int
    {
        $customerId = $this->createNewCustomerWithResource();

        $this->authenticatedApiRequest('DELETE', '/api/customers/' . $customerId);
        $response = $this->client->getResponse();

        static::assertSame('application/json', $response->headers->get('Content-Type'));
        static::assertSame(200, $response->getStatusCode());

        $result = $response->getContent();
        static::assertIsString($result);
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertTrue($result['success']);

        return $customerId;
    }

    public function testDeleteCustomersWithInvalidIdShouldReturnMessage(): void
    {
        $id = 99999999;

        $this->authenticatedApiRequest('DELETE', '/api/customers/' . $id);
        $response = $this->client->getResponse();

        static::assertSame('application/json', $response->headers->get('Content-Type'));
        static::assertSame(404, $response->getStatusCode());

        $result = $response->getContent();
        static::assertIsString($result);
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);

        static::assertArrayHasKey('message', $result);
    }

    public function testPutCustomersWithInvalidIdShouldReturnMessage(): void
    {
        $id = 99999999;

        $this->authenticatedApiRequest('PUT', '/api/customers/' . $id);
        $response = $this->client->getResponse();

        static::assertSame('application/json', $response->headers->get('Content-Type'));
        static::assertSame(404, $response->getStatusCode());

        $result = $response->getContent();
        static::assertIsString($result);
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);

        static::assertArrayHasKey('message', $result);
    }

    public function testGetCustomersShouldBeSuccessful(): void
    {
        $this->authenticatedApiRequest('GET', '/api/customers/');
        $response = $this->client->getResponse();
        static::assertInstanceOf(Enlight_Controller_Response_ResponseTestCase::class, $response);

        static::assertSame('application/json', $response->getHeader('Content-Type'));
        static::assertSame(200, $response->getStatusCode());

        $response = $response->getContent();
        static::assertIsString($response);
        $response = json_decode($response, true);

        static::assertArrayHasKey('success', $response);
        static::assertTrue($response['success']);

        static::assertArrayHasKey('data', $response);

        static::assertArrayHasKey('total', $response);
        static::assertIsInt($response['total']);

        $data = $response['data'];
        static::assertIsArray($data);
    }

    public function testGetCustomerWithNumberAsIdWithDuplicateNumberThrowsException(): void
    {
        $existingCustomer = $this->getContainer()->get(Connection::class)->createQueryBuilder()
            ->select(['id', 'customernumber'])
            ->from('s_user')
            ->setMaxResults(1)
            ->execute()
            ->fetchAllKeyValue();
        $existingCustomerId = array_key_first($existingCustomer);
        $existingCustomerNumber = $existingCustomer[$existingCustomerId];

        $newCustomerId = $this->createNewCustomerWithResource($existingCustomerNumber);

        $customersWithSameNumber = $this->getContainer()->get(Connection::class)->createQueryBuilder()
            ->select('id')
            ->from('s_user')
            ->where('customernumber = ' . $existingCustomerNumber)
            ->execute()
            ->fetchFirstColumn();

        static::assertCount(2, $customersWithSameNumber);

        $this->authenticatedApiRequest('DELETE', sprintf('/api/customers/%d?useNumberAsId=1', $existingCustomerNumber));
        $response = $this->client->getResponse();

        static::assertInstanceOf(Enlight_Controller_Response_ResponseTestCase::class, $response);

        static::assertSame('application/json', $response->getHeader('Content-Type'));
        static::assertSame(Response::HTTP_CONFLICT, $response->getStatusCode());

        $response = $response->getContent();
        static::assertIsString($response);
        $response = json_decode($response, true);

        static::assertFalse($response['success']);
        static::assertSame(
            sprintf(
                "Identifier 'number' with value '%s' for entity 'Shopware\Models\Customer\Customer' is not unique.",
                $existingCustomerNumber
            ),
            $response['message']
        );

        static::assertContains($existingCustomerId, $response['foundIds']);
        static::assertContains($newCustomerId, $response['foundIds']);
    }

    /**
     * @param array<string, array<array<string, int|string>>>|null $paymentData
     */
    private function createNewCustomerWithResource(?string $customerNumber = null, ?array $paymentData = null): int
    {
        $requestData = $this->getCustomerRequestData($customerNumber, $paymentData);

        return $this->getContainer()->get(CustomerResource::class)->create($requestData)->getId();
    }

    /**
     * @param array<string, array<array<string, int|string>>>|null $paymentData
     *
     * @return array<string, mixed>
     */
    private function getCustomerRequestData(?string $customerNumber = null, ?array $paymentData = null): array
    {
        $date = new DateTime();
        $date->modify('-10 days');
        $firstlogin = $date->format(DateTime::ATOM);

        $date->modify('+2 day');
        $lastlogin = $date->format(DateTime::ATOM);

        $birthday = DateTime::createFromFormat('Y-m-d', '1986-12-20');
        static::assertInstanceOf(DateTimeInterface::class, $birthday);
        $birthday = $birthday->format(DateTime::ATOM);

        $requestData = [
            'password' => 'superSecurePassword',
            'number' => $customerNumber,
            'active' => true,
            'email' => 'test@foobar.com',

            'firstlogin' => $firstlogin,
            'lastlogin' => $lastlogin,
            'paymentId' => 2,

            'salutation' => 'mr',
            'firstname' => 'Max',
            'lastname' => 'Mustermann',
            'birthday' => $birthday,

            'billing' => [
                'salutation' => 'Mr',
                'firstName' => 'Max',
                'lastName' => 'Mustermann',
                'country' => 2,
                'street' => 'Fake street 123',
                'city' => 'City',
                'zipcode' => 55555,
            ],

            'shipping' => [
                'salutation' => 'Mr',
                'company' => 'Widgets Inc.',
                'firstName' => 'Max',
                'lastName' => 'Mustermann',
                'country' => 2,
                'street' => 'Fake street 123',
                'city' => 'City',
                'zipcode' => 55555,
            ],
        ];

        if (\is_array($paymentData)) {
            $requestData = array_merge($requestData, $paymentData);
        } else {
            $requestData['debit'] = [
                'account' => 'Fake Account',
                'bankCode' => '55555555',
                'bankName' => 'Fake Bank',
                'accountHolder' => 'Max Mustermann',
            ];
        }

        return $requestData;
    }
}

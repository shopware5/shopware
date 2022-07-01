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

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Connection;
use Enlight_Components_Session_Namespace;
use Enlight_Controller_Front;
use Enlight_Controller_Request_Request;
use Enlight_Controller_Request_RequestHttp;
use Generator;
use PHPUnit\Framework\TestCase;
use sAdmin;
use sBasket;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Components\Api\Resource\Customer as CustomerResource;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Random;
use Shopware\Models\Customer\Customer;
use Shopware\Models\Customer\Group;
use Shopware\Models\Dispatch\Dispatch;
use Shopware\Models\Payment\Payment;
use Shopware\Models\Tax\Rule;
use Shopware\Models\Tax\Tax;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;
use Shopware_Components_Config;
use Shopware_Components_Snippet_Manager;
use ShopwarePlugin\PaymentMethods\Components\BasePaymentMethod;

class AdminTest extends TestCase
{
    use ContainerTrait;
    use DatabaseTransactionBehaviour;

    private const DEFAULT_SHIPPING_METHOD_ID = 9;
    private const DEFAULT_CUSTOMER_GROUP_ID = 1;

    private sAdmin $module;

    private sBasket $basketModule;

    private Shopware_Components_Config $config;

    private Enlight_Components_Session_Namespace $session;

    private Shopware_Components_Snippet_Manager $snippetManager;

    private Enlight_Controller_Front $front;

    private Connection $connection;

    public function setUp(): void
    {
        parent::setUp();

        $this->getContainer()->get(ModelManager::class)->clear();
        $this->getContainer()->get('front')->setRequest(new Enlight_Controller_Request_RequestHttp());

        $this->module = $this->getContainer()->get('modules')->Admin();
        $this->config = $this->getContainer()->get('config');
        $this->session = $this->getContainer()->get('session');
        $this->front = $this->getContainer()->get('front');
        $this->snippetManager = $this->getContainer()->get('snippets');
        $this->basketModule = $this->getContainer()->get('modules')->Basket();
        $this->connection = $this->getContainer()->get(Connection::class);
        $this->session->offsetSet('sessionId', null);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->getContainer()->get(ModelManager::class)->clear();
    }

    public function testsGetPaymentMeanById(): void
    {
        // Fetching non-existing payment means returns null
        static::assertEmpty($this->module->sGetPaymentMeanById(0));

        // Fetching existing inactive payment means returns the data array
        $sepaData = $this->module->sGetPaymentMeanById(6);
        static::assertIsArray($sepaData);
        static::assertArrayHasKey('id', $sepaData);
        static::assertArrayHasKey('name', $sepaData);
        static::assertArrayHasKey('description', $sepaData);
        static::assertArrayHasKey('debit_percent', $sepaData);
        static::assertArrayHasKey('surcharge', $sepaData);
        static::assertArrayHasKey('surchargestring', $sepaData);
        static::assertArrayHasKey('active', $sepaData);
        static::assertArrayHasKey('esdactive', $sepaData);

        // Fetching existing active payment means returns the data array
        $debitData = $this->module->sGetPaymentMeanById(2);
        static::assertIsArray($debitData);
        static::assertArrayHasKey('id', $debitData);
        static::assertArrayHasKey('name', $debitData);
        static::assertArrayHasKey('description', $debitData);
        static::assertArrayHasKey('debit_percent', $debitData);
        static::assertArrayHasKey('surcharge', $debitData);
        static::assertArrayHasKey('surchargestring', $debitData);
        static::assertArrayHasKey('active', $debitData);
        static::assertArrayHasKey('esdactive', $debitData);

        $customer = $this->createDummyCustomer();

        static::assertSame((int) $this->config->get('defaultPayment'), $customer->getPaymentId());

        $this->deleteDummyCustomer($customer);
        $this->session->offsetSet('sUserId', null);
    }

    public function testsGetPaymentMeans(): void
    {
        foreach ($this->module->sGetPaymentMeans() as $paymentMean) {
            static::assertArrayHasKey('id', $paymentMean);
            static::assertArrayHasKey('name', $paymentMean);
            static::assertArrayHasKey('description', $paymentMean);
            static::assertArrayHasKey('debit_percent', $paymentMean);
            static::assertArrayHasKey('surcharge', $paymentMean);
            static::assertArrayHasKey('surchargestring', $paymentMean);
            static::assertArrayHasKey('active', $paymentMean);
            static::assertArrayHasKey('esdactive', $paymentMean);
            static::assertContains((int) $paymentMean['id'], [3, 5, 6]);
        }
    }

    public function testsInitiatePaymentClass(): void
    {
        $payments = $this->getContainer()->get(ModelManager::class)->getRepository(Payment::class)->findAll();

        foreach ($payments as $payment) {
            $paymentClass = $this->module->sInitiatePaymentClass($this->module->sGetPaymentMeanById($payment->getId()));
            static::assertInstanceOf(BasePaymentMethod::class, $paymentClass);
            $this->getContainer()->get('front')->setRequest(new Enlight_Controller_Request_RequestHttp());

            $request = $this->getContainer()->get('front')->Request();
            static::assertInstanceOf(Enlight_Controller_Request_Request::class, $request);
            $requestData = $request->getParams();
            $validationResult = $paymentClass->validate($requestData);
            static::assertIsArray($validationResult);
            if (\count($validationResult)) {
                static::assertArrayHasKey('sErrorFlag', $validationResult);
                static::assertArrayHasKey('sErrorMessages', $validationResult);
            }
        }
    }

    public function testExceptionInsValidateStep3(): void
    {
        $this->expectException('Enlight_Exception');
        $this->expectExceptionMessage('sValidateStep3 #00: No payment id');
        $this->module->sValidateStep3();
    }

    public function testsValidateStep3(): void
    {
        $this->getRequest()->setPost('sPayment', 2);

        $result = $this->module->sValidateStep3();
        static::assertArrayHasKey('checkPayment', $result);
        static::assertArrayHasKey('paymentData', $result);
        static::assertArrayHasKey('sProcessed', $result);
        static::assertArrayHasKey('sPaymentObject', $result);

        static::assertIsArray($result['checkPayment']);
        static::assertCount(2, $result['checkPayment']);
        static::assertIsArray($result['paymentData']);
        static::assertCount(21, $result['paymentData']);
        static::assertIsBool($result['sProcessed']);
        static::assertTrue($result['sProcessed']);
        static::assertIsObject($result['sPaymentObject']);
        static::assertInstanceOf(BasePaymentMethod::class, $result['sPaymentObject']);
    }

    public function testsUpdateNewsletter(): void
    {
        $email = uniqid((string) rand()) . 'test@foobar.com';

        // Test insertion
        static::assertTrue($this->module->sUpdateNewsletter(true, $email));
        $newsletterSubscription = $this->connection->fetchAssociative(
            'SELECT * FROM s_campaigns_mailaddresses WHERE email = ?',
            [$email]
        );
        static::assertIsArray($newsletterSubscription);
        static::assertSame(0, (int) $newsletterSubscription['customer']);
        static::assertSame(self::DEFAULT_CUSTOMER_GROUP_ID, (int) $newsletterSubscription['groupID']);

        // Test removal
        static::assertTrue($this->module->sUpdateNewsletter(false, $email));
        $newsletterSubscription = $this->connection->fetchAssociative(
            'SELECT * FROM s_campaigns_mailaddresses WHERE email = ?',
            [$email]
        );
        static::assertFalse($newsletterSubscription);

        // Retest insertion for customers
        static::assertTrue($this->module->sUpdateNewsletter(true, $email, true));
        $newsletterSubscription = $this->connection->fetchAssociative(
            'SELECT * FROM s_campaigns_mailaddresses WHERE email = ?',
            [$email]
        );
        static::assertIsArray($newsletterSubscription);
        static::assertSame(1, (int) $newsletterSubscription['customer']);
        static::assertSame(0, (int) $newsletterSubscription['groupID']);

        // Test removal
        static::assertTrue($this->module->sUpdateNewsletter(false, $email));
        $newsletterSubscription = $this->connection->fetchAssociative(
            'SELECT * FROM s_campaigns_mailaddresses WHERE email = ?',
            [$email]
        );
        static::assertFalse($newsletterSubscription);
    }

    public function testsUpdatePayment(): void
    {
        // Test no user id
        static::assertFalse($this->module->sUpdatePayment());

        $customer = $this->createDummyCustomer();
        $this->session->offsetSet('sUserId', $customer->getId());

        // Test that operation succeeds even without payment id
        static::assertTrue($this->module->sUpdatePayment());
        static::assertSame(
            0,
            (int) $this->connection->fetchOne('SELECT paymentID FROM s_user WHERE id = ?', [$customer->getId()])
        );

        // Setup dummy test data and test with it
        $this->getRequest()->setPost([
            'sPayment' => 2,
        ]);
        static::assertTrue($this->module->sUpdatePayment());
        static::assertSame(
            2,
            (int) $this->connection->fetchOne('SELECT paymentID FROM s_user WHERE id = ?', [$customer->getId()])
        );

        $this->deleteDummyCustomer($customer);
        $this->session->offsetSet('sUserId', null);
    }

    public function testsLogin(): void
    {
        // Test with no data, get error
        $result = $this->module->sLogin();
        static::assertIsArray($result);
        static::assertArrayHasKey('sErrorFlag', $result);
        static::assertArrayHasKey('sErrorMessages', $result);
        static::assertCount(1, $result['sErrorMessages']);
        static::assertContains(
            $this->snippetManager->getNamespace('frontend/account/internalMessages')
                ->get('LoginFailure', 'Wrong email or password'),
            $result['sErrorMessages']
        );
        static::assertCount(2, $result['sErrorFlag']);
        static::assertArrayHasKey('email', $result['sErrorFlag']);
        static::assertArrayHasKey('password', $result['sErrorFlag']);

        // Test with wrong data, get error
        $this->getRequest()->setPost([
            'email' => uniqid((string) rand()) . 'test',
            'password' => uniqid((string) rand()) . 'test',
        ]);
        $result = $this->module->sLogin();
        static::assertIsArray($result);
        static::assertArrayHasKey('sErrorFlag', $result);
        static::assertArrayHasKey('sErrorMessages', $result);
        static::assertCount(1, $result['sErrorMessages']);
        static::assertContains(
            $this->snippetManager->getNamespace('frontend/account/internalMessages')
                ->get('LoginFailure', 'Wrong email or password'),
            $result['sErrorMessages']
        );
        static::assertNull($result['sErrorFlag']);

        $customer = $this->createDummyCustomer();

        // Test successful login
        $this->getRequest()->setPost([
            'email' => $customer->getEmail(),
            'password' => 'fooobar',
        ]);
        $result = $this->module->sLogin();
        static::assertIsArray($result);
        static::assertArrayHasKey('sErrorFlag', $result);
        static::assertArrayHasKey('sErrorMessages', $result);
        static::assertNull($result['sErrorFlag']);
        static::assertNull($result['sErrorMessages']);

        // Test wrong pre-hashed password. Need a user with md5 encoded password
        $this->connection->update(
            's_user',
            [
                'password' => md5('fooobar'),
                'encoder' => 'md5',
            ],
            ['id' => $customer->getId()]
        );

        $this->getRequest()->setPost([
            'email' => $customer->getEmail(),
            'passwordMD5' => uniqid((string) rand()),
        ]);
        $result = $this->module->sLogin(true);
        static::assertIsArray($result);
        static::assertArrayHasKey('sErrorFlag', $result);
        static::assertArrayHasKey('sErrorMessages', $result);
        static::assertNull($result['sErrorFlag']);
        static::assertCount(1, $result['sErrorMessages']);
        static::assertContains(
            $this->snippetManager->getNamespace('frontend/account/internalMessages')
                ->get('LoginFailure', 'Wrong email or password'),
            $result['sErrorMessages']
        );

        // Test correct pre-hashed password
        $this->getRequest()->setPost([
            'email' => $customer->getEmail(),
            'passwordMD5' => md5('fooobar'),
        ]);
        $result = $this->module->sLogin(true);
        static::assertIsArray($result);
        static::assertArrayHasKey('sErrorFlag', $result);
        static::assertArrayHasKey('sErrorMessages', $result);
        static::assertNull($result['sErrorFlag']);
        static::assertNull($result['sErrorMessages']);

        $modifiedMd5User = $this->connection->fetchAssociative(
            'SELECT * FROM s_user WHERE id = ?',
            [$customer->getId()]
        );
        static::assertIsArray($modifiedMd5User);

        // Test that it's the same user, but with different last login
        static::assertSame($modifiedMd5User['email'], $customer->getEmail());
        static::assertSame($modifiedMd5User['password'], md5('fooobar'));
        static::assertNotEquals($modifiedMd5User['lastlogin'], $customer->getLastLogin() ? $customer->getLastLogin()->format('Y-m-d H:i:s') : null);

        // Test inactive account
        $this->connection->update('s_user', ['active' => 0], ['id' => $customer->getId()]);
        $result = $this->module->sLogin(true);
        static::assertIsArray($result);
        static::assertArrayHasKey('sErrorFlag', $result);
        static::assertArrayHasKey('sErrorMessages', $result);
        static::assertNull($result['sErrorFlag']);
        static::assertCount(1, $result['sErrorMessages']);
        static::assertContains(
            $this->snippetManager->getNamespace('frontend/account/internalMessages')
                ->get(
                    'LoginFailureActive',
                    'Your account is disabled. Please contact us.'
                ),
            $result['sErrorMessages']
        );

        // Test brute force lockout
        $this->connection->update('s_user', ['active' => 1], ['id' => $customer->getId()]);
        $this->getRequest()->setPost([
            'email' => $customer->getEmail(),
            'password' => 'asasasasas',
        ]);
        $this->module->sLogin();
        $this->module->sLogin();
        $this->module->sLogin();
        $this->module->sLogin();
        $this->module->sLogin();
        $this->module->sLogin();
        $this->module->sLogin();
        $this->module->sLogin();
        $this->module->sLogin();
        $result = $this->module->sLogin();
        static::assertIsArray($result);
        static::assertArrayHasKey('sErrorFlag', $result);
        static::assertArrayHasKey('sErrorMessages', $result);
        static::assertNull($result['sErrorFlag']);
        static::assertCount(1, $result['sErrorMessages']);
        static::assertContains(
            $this->snippetManager->getNamespace('frontend/account/internalMessages')
                ->get(
                    'LoginFailureLocked',
                    'Too many failed logins. Your account was temporary deactivated.'
                ),
            $result['sErrorMessages']
        );

        $this->deleteDummyCustomer($customer);
        $this->session->offsetSet('sUserId', null);
    }

    public function testsCheckUser(): void
    {
        $customer = $this->createDummyCustomer();

        // Basic failing case
        static::assertFalse($this->module->sCheckUser());

        // Test successful login
        $this->getRequest()->setPost([
            'email' => $customer->getEmail(),
            'password' => 'fooobar',
        ]);
        $result = $this->module->sLogin();
        static::assertIsArray($result);
        static::assertArrayHasKey('sErrorFlag', $result);
        static::assertArrayHasKey('sErrorMessages', $result);
        static::assertNull($result['sErrorFlag']);
        static::assertNull($result['sErrorMessages']);

        // Test that user is correctly logged in
        static::assertTrue($this->module->sCheckUser());

        // Force timeout
        $this->connection->update('s_user', ['lastlogin' => '2000-01-01 00:00:00'], ['id' => $customer->getId()]);
        static::assertFalse($this->module->sCheckUser());

        $customerGroup = $customer->getGroup();
        static::assertInstanceOf(Group::class, $customerGroup);
        static::assertSame($customerGroup->getKey(), $this->session->offsetGet('sUserGroup'));
        static::assertIsArray($this->session->offsetGet('sUserGroupData'));
        static::assertArrayHasKey('groupkey', $this->session->offsetGet('sUserGroupData'));
        static::assertArrayHasKey('description', $this->session->offsetGet('sUserGroupData'));
        static::assertArrayHasKey('tax', $this->session->offsetGet('sUserGroupData'));
        static::assertArrayHasKey('taxinput', $this->session->offsetGet('sUserGroupData'));
        static::assertArrayHasKey('mode', $this->session->offsetGet('sUserGroupData'));
        static::assertArrayHasKey('discount', $this->session->offsetGet('sUserGroupData'));
        static::assertArrayHasKey('minimumorder', $this->session->offsetGet('sUserGroupData'));
        static::assertArrayHasKey('minimumordersurcharge', $this->session->offsetGet('sUserGroupData'));

        $this->deleteDummyCustomer($customer);
        $this->session->offsetSet('sUserId', null);
    }

    public function testsGetCountryTranslation(): void
    {
        // Backup existing data and inject demo data
        $existingData = $this->connection->fetchAssociative("
            SELECT * FROM s_core_translations
            WHERE objecttype = 'config_countries' AND objectlanguage = 2
        ");

        $demoData = [
            'objectkey' => 1,
            'objectlanguage' => 2,
            'objecttype' => 'config_countries',
            'objectdata' => serialize(
                [
                    2 => [
                        'active' => '1',
                        'countryname' => 'Germany',
                    ],
                    5 => [
                        'active' => '1',
                        'countryname' => 'Belgium',
                    ],
                ]
            ),
        ];

        if (\is_array($existingData)) {
            $this->connection->update('s_core_translations', $demoData, ['id' => $existingData['id']]);
        } else {
            $this->connection->insert('s_core_translations', $demoData);
        }

        // Test loading all data, should return the test data
        $shopId = $this->getContainer()->get(ContextServiceInterface::class)->getShopContext()->getShop()->getId();
        $this->getContainer()->get(ContextServiceInterface::class)->getShopContext()->getShop()->setId(2);

        $result = $this->module->sGetCountryTranslation();
        static::assertCount(2, $result);
        static::assertArrayHasKey(2, $result);
        static::assertArrayHasKey(5, $result);
        static::assertArrayHasKey('active', $result[2]);
        static::assertArrayHasKey('countryname', $result[2]);
        static::assertSame(1, (int) $result[2]['active']);
        static::assertSame('Germany', $result[2]['countryname']);
        static::assertArrayHasKey('active', $result[5]);
        static::assertArrayHasKey('countryname', $result[5]);
        static::assertSame(1, (int) $result[5]['active']);
        static::assertSame('Belgium', $result[5]['countryname']);

        // Test with just one country
        $result = $this->module->sGetCountryTranslation(['id' => 2, 'randomField' => 'randomValue']);
        static::assertCount(4, $result);
        static::assertArrayHasKey('id', $result);
        static::assertArrayHasKey('active', $result);
        static::assertArrayHasKey('countryname', $result);
        static::assertArrayHasKey('randomField', $result);
        static::assertSame(2, $result['id']);
        static::assertSame(1, (int) $result['active']);
        static::assertSame('Germany', $result['countryname']);
        static::assertSame('randomValue', $result['randomField']);

        // If backup data exists, restore it
        if ($existingData) {
            $existingDataId = $existingData['id'];
            unset($existingData['id']);
            $this->connection->update('s_core_translations', $existingData, ['id' => $existingDataId]);
        }

        $this->getContainer()->get(ContextServiceInterface::class)->getShopContext()->getShop()->setId($shopId);
    }

    public function testsGetDispatchTranslation(): void
    {
        // Backup existing data and inject demo data
        $existingData = $this->connection->fetchAssociative("
            SELECT * FROM s_core_translations
            WHERE objecttype = 'config_dispatch' AND objectlanguage = 2
        ");

        $demoData = [
            'objectkey' => 1,
            'objectlanguage' => 2,
            'objecttype' => 'config_dispatch',
            'objectdata' => serialize(
                [
                    self::DEFAULT_SHIPPING_METHOD_ID => [
                        'dispatch_name' => 'Standard shipping',
                        'dispatch_description' => 'Standard shipping description',
                        'dispatch_status_link' => 'http://www.dhl.com',
                    ],
                    10 => [
                        'dispatch_name' => 'Shipping by weight',
                        'dispatch_description' => 'Shipping by weight description',
                        'dispatch_status_link' => 'url',
                    ],
                ]
            ),
        ];

        if (\is_array($existingData)) {
            $this->connection->update('s_core_translations', $demoData, ['id' => $existingData['id']]);
        } else {
            $this->connection->insert('s_core_translations', $demoData);
        }

        // Test loading all data, should return the test data
        $shopId = $this->getContainer()->get(ContextServiceInterface::class)->getShopContext()->getShop()->getId();
        $this->getContainer()->get(ContextServiceInterface::class)->getShopContext()->getShop()->setId(2);

        $result = $this->module->sGetDispatchTranslation();
        static::assertCount(2, $result);
        static::assertArrayHasKey(self::DEFAULT_SHIPPING_METHOD_ID, $result);
        static::assertArrayHasKey(10, $result);
        static::assertArrayHasKey('dispatch_name', $result[self::DEFAULT_SHIPPING_METHOD_ID]);
        static::assertArrayHasKey('dispatch_description', $result[self::DEFAULT_SHIPPING_METHOD_ID]);
        static::assertArrayHasKey('dispatch_status_link', $result[self::DEFAULT_SHIPPING_METHOD_ID]);
        static::assertArrayHasKey('dispatch_name', $result[10]);
        static::assertArrayHasKey('dispatch_description', $result[10]);
        static::assertArrayHasKey('dispatch_status_link', $result[10]);
        static::assertSame('Standard shipping', $result[self::DEFAULT_SHIPPING_METHOD_ID]['dispatch_name']);
        static::assertSame('Standard shipping description', $result[self::DEFAULT_SHIPPING_METHOD_ID]['dispatch_description']);
        static::assertSame('http://www.dhl.com', $result[self::DEFAULT_SHIPPING_METHOD_ID]['dispatch_status_link']);
        static::assertSame('Shipping by weight', $result[10]['dispatch_name']);
        static::assertSame('Shipping by weight description', $result[10]['dispatch_description']);
        static::assertSame('url', $result[10]['dispatch_status_link']);

        // Test with just one shipping method
        $result = $this->module->sGetDispatchTranslation(['id' => self::DEFAULT_SHIPPING_METHOD_ID, 'randomField' => 'randomValue']);
        static::assertCount(5, $result);
        static::assertArrayHasKey('id', $result);
        static::assertArrayHasKey('name', $result);
        static::assertArrayHasKey('description', $result);
        static::assertArrayHasKey('status_link', $result);
        static::assertArrayHasKey('randomField', $result);
        static::assertSame(self::DEFAULT_SHIPPING_METHOD_ID, $result['id']);
        static::assertSame('Standard shipping', $result['name']);
        static::assertSame('Standard shipping description', $result['description']);
        static::assertSame('http://www.dhl.com', $result['status_link']);
        static::assertSame('randomValue', $result['randomField']);

        // If backup data exists, restore it
        if ($existingData) {
            $existingDataId = $existingData['id'];
            unset($existingData['id']);
            $this->connection->update('s_core_translations', $existingData, ['id' => $existingDataId]);
        }

        $this->getContainer()->get(ContextServiceInterface::class)->getShopContext()->getShop()->setId($shopId);
    }

    public function testsGetPaymentTranslation(): void
    {
        // Backup existing data and inject demo data
        $existingData = $this->connection->fetchAssociative("
            SELECT * FROM s_core_translations
            WHERE objecttype = 'config_payment' AND objectlanguage = 2
        ");

        $demoData = [
            'objectkey' => 1,
            'objectlanguage' => 2,
            'objecttype' => 'config_payment',
            'objectdata' => serialize(
                [
                    4 => [
                        'description' => 'Invoice',
                        'additionalDescription' => 'Payment by invoice. Shopware provides automatic invoicing for all customers on orders after the first, in order to avoid defaults on payment.',
                    ],
                    2 => [
                        'description' => 'Debit',
                        'additionalDescription' => 'Additional text',
                    ],
                    3 => [
                        'description' => 'Cash on delivery',
                        'additionalDescription' => '(including 2.00 Euro VAT)',
                    ],
                    5 => [
                        'description' => 'Paid in advance',
                        'additionalDescription' => 'The goods are delivered directly upon receipt of payment.',
                    ],
                    6 => [
                        'additionalDescription' => 'SEPA direct debit',
                    ],
                ]
            ),
        ];

        if (\is_array($existingData)) {
            $this->connection->update('s_core_translations', $demoData, ['id' => $existingData['id']]);
        } else {
            $this->connection->insert('s_core_translations', $demoData);
        }

        // Test loading all data, should return the test data
        $shopId = $this->getContainer()->get(ContextServiceInterface::class)->getShopContext()->getShop()->getId();
        $this->getContainer()->get(ContextServiceInterface::class)->getShopContext()->getShop()->setId(2);

        $result = $this->module->sGetPaymentTranslation();
        static::assertCount(5, $result);
        static::assertArrayHasKey(2, $result);
        static::assertArrayHasKey(3, $result);
        static::assertArrayHasKey(4, $result);
        static::assertArrayHasKey(5, $result);
        static::assertArrayHasKey(6, $result);
        static::assertArrayHasKey('description', $result[2]);
        static::assertArrayHasKey('additionalDescription', $result[2]);
        static::assertArrayHasKey('description', $result[3]);
        static::assertArrayHasKey('additionalDescription', $result[3]);
        static::assertArrayHasKey('description', $result[5]);
        static::assertArrayHasKey('additionalDescription', $result[5]);
        static::assertSame('Debit', $result[2]['description']);
        static::assertSame('Additional text', $result[2]['additionalDescription']);
        static::assertSame('Cash on delivery', $result[3]['description']);
        static::assertSame('(including 2.00 Euro VAT)', $result[3]['additionalDescription']);
        static::assertSame('Paid in advance', $result[5]['description']);
        static::assertSame('The goods are delivered directly upon receipt of payment.', $result[5]['additionalDescription']);

        // Test with just one payment mean
        $result = $this->module->sGetPaymentTranslation(['id' => 2, 'randomField' => 'randomValue']);
        static::assertCount(4, $result);
        static::assertArrayHasKey('id', $result);
        static::assertArrayHasKey('description', $result);
        static::assertArrayHasKey('additionaldescription', $result);
        static::assertArrayHasKey('randomField', $result);
        static::assertSame(2, $result['id']);
        static::assertSame('Debit', $result['description']);
        static::assertSame('Additional text', $result['additionaldescription']);
        static::assertSame('randomValue', $result['randomField']);

        // If backup data exists, restore it
        if ($existingData) {
            $existingDataId = $existingData['id'];
            unset($existingData['id']);
            $this->connection->update('s_core_translations', $existingData, ['id' => $existingDataId]);
        }

        $this->getContainer()->get(ContextServiceInterface::class)->getShopContext()->getShop()->setId($shopId);
    }

    public function testsGetCountryStateTranslation(): void
    {
        // Backup existing data and inject demo data
        $existingData = $this->connection->fetchAssociative("
            SELECT * FROM s_core_translations
            WHERE objecttype = 'config_country_states' AND objectlanguage = 1
        ");

        $demoData = [
            'objectkey' => 1,
            'objectlanguage' => 1,
            'objecttype' => 'config_country_states',
            'objectdata' => serialize(
                [
                    24 => [
                        'name' => 'California',
                    ],
                    23 => [
                        'name' => 'Arkansas (english)',
                    ],
                ]
            ),
        ];

        if (\is_array($existingData)) {
            $this->connection->update('s_core_translations', $demoData, ['id' => $existingData['id']]);
        } else {
            $this->connection->insert('s_core_translations', $demoData);
        }

        // Test with default shop, return empty array
        static::assertCount(0, $this->module->sGetCountryStateTranslation());

        // Hack the current system shop, so we can properly test this
        $this->getContainer()->get('shop')->setDefault(false);

        $result = $this->module->sGetCountryStateTranslation();
        static::assertCount(2, $result);
        static::assertArrayHasKey(23, $result);
        static::assertArrayHasKey(24, $result);
        static::assertArrayHasKey('name', $result[23]);
        static::assertArrayHasKey('name', $result[24]);
        static::assertSame('Arkansas (english)', $result[23]['name']);
        static::assertSame('California', $result[24]['name']);

        // Create a stub of a Shop for fallback.
        $shopFallbackId = $this->getContainer()->get(ContextServiceInterface::class)->getShopContext()->getShop()->getFallbackId();
        $this->getContainer()->get(ContextServiceInterface::class)->getShopContext()->getShop()->setFallbackId(10000);

        $this->connection->insert('s_core_translations', [
            'objectkey' => 1,
            'objectlanguage' => 10000,
            'objecttype' => 'config_country_states',
            'objectdata' => serialize(
                [
                    2 => [
                        'name' => 'asdfasfdasdfa',
                    ],
                ]
            ),
        ]);

        // Test with fallback
        $result = $this->module->sGetCountryStateTranslation();
        static::assertCount(3, $result);
        static::assertArrayHasKey(2, $result);
        static::assertArrayHasKey(23, $result);
        static::assertArrayHasKey(24, $result);
        static::assertArrayHasKey('name', $result[2]);
        static::assertArrayHasKey('name', $result[23]);
        static::assertArrayHasKey('name', $result[24]);
        static::assertSame('asdfasfdasdfa', $result[2]['name']);
        static::assertSame('Arkansas (english)', $result[23]['name']);
        static::assertSame('California', $result[24]['name']);

        // If backup data exists, restore it
        if ($existingData) {
            $existingDataId = $existingData['id'];
            unset($existingData['id']);
            $this->connection->update('s_core_translations', $existingData, ['id' => $existingDataId]);
        }
        $this->connection->delete('s_core_translations', ['objectlanguage' => 10000]);

        $this->getContainer()->get(ContextServiceInterface::class)->getShopContext()->getShop()->setFallbackId($shopFallbackId);
    }

    public function testsGetCountryList(): void
    {
        // Test with default country data
        $result = $this->module->sGetCountryList();
        foreach ($result as $country) {
            static::assertArrayHasKey('id', $country);
            static::assertArrayHasKey('countryname', $country);
            static::assertArrayHasKey('countryiso', $country);
            static::assertArrayHasKey('areaID', $country);
            static::assertArrayHasKey('countryen', $country);
            static::assertArrayHasKey('taxfree', $country);
            static::assertArrayHasKey('display_state_in_registration', $country);
            static::assertArrayHasKey('force_state_in_registration', $country);
            static::assertArrayHasKey('states', $country);
            static::assertArrayHasKey('flag', $country);
        }

        // Add translations
        $existingCountryData = $this->connection->fetchAssociative("
            SELECT * FROM s_core_translations
            WHERE objecttype = 'config_countries' AND objectlanguage = 1
        ");
        $existingStateData = $this->connection->fetchAssociative("
            SELECT * FROM s_core_translations
            WHERE objecttype = 'config_country_states' AND objectlanguage = 1
        ");

        $demoCountryData = [
            'objectkey' => 1,
            'objectlanguage' => 1,
            'objecttype' => 'config_countries',
            'objectdata' => serialize(
                [
                    2 => [
                        'active' => '1',
                        'countryname' => 'Germany',
                    ],
                ]
            ),
        ];
        $demoStateData = [
            'objectkey' => 1,
            'objectlanguage' => 1,
            'objecttype' => 'config_country_states',
            'objectdata' => serialize(
                [
                    2 => [
                        'name' => '111',
                    ],
                    3 => [
                        'name' => '222',
                    ],
                ]
            ),
        ];

        if (\is_array($existingCountryData)) {
            $this->connection->update('s_core_translations', $demoCountryData, ['id' => $existingCountryData['id']]);
        } else {
            $this->connection->insert('s_core_translations', $demoCountryData);
        }
        if (\is_array($existingStateData)) {
            $this->connection->update('s_core_translations', $demoStateData, ['id' => $existingStateData['id']]);
        } else {
            $this->connection->insert('s_core_translations', $demoStateData);
        }

        // Hack current context, so next test works
        $context = $this->getContainer()->get('shopware_storefront.context_service')->getShopContext();
        $context->getShop()->setIsDefault(false);

        // Test with translations but display_states = false
        $result = $this->module->sGetCountryList();
        $country = array_shift($result); // Germany
        static::assertArrayHasKey('id', $country);
        static::assertArrayHasKey('countryname', $country);
        static::assertArrayHasKey('countryiso', $country);
        static::assertArrayHasKey('areaID', $country);
        static::assertArrayHasKey('countryen', $country);
        static::assertArrayHasKey('taxfree', $country);
        static::assertArrayHasKey('display_state_in_registration', $country);
        static::assertArrayHasKey('force_state_in_registration', $country);
        static::assertArrayHasKey('states', $country);
        static::assertArrayHasKey('flag', $country);
        static::assertCount(0, $country['states']);
        static::assertSame('Germany', $country['countryname']);

        // Make Germany display states, so we can test it
        $existingGermanyData = $this->connection->fetchAssociative("
            SELECT * FROM s_core_countries
            WHERE countryiso = 'DE'
        ");
        static::assertIsArray($existingGermanyData);
        $this->connection->update(
            's_core_countries',
            ['display_state_in_registration' => 1],
            ['id' => $existingGermanyData['id']]
        );

        // Test with translations and states
        $result = $this->module->sGetCountryList();
        $country = $result[2]; // Germany with ID 2
        static::assertArrayHasKey('id', $country);
        static::assertArrayHasKey('countryname', $country);
        static::assertArrayHasKey('countryiso', $country);
        static::assertArrayHasKey('areaID', $country);
        static::assertArrayHasKey('countryen', $country);
        static::assertArrayHasKey('taxfree', $country);
        static::assertArrayHasKey('display_state_in_registration', $country);
        static::assertArrayHasKey('force_state_in_registration', $country);
        static::assertArrayHasKey('states', $country);
        static::assertArrayHasKey('flag', $country);
        static::assertCount(16, $country['states']);
        static::assertSame('Germany', $country['countryname']);
        foreach ($country['states'] as $state) {
            static::assertArrayHasKey('id', $state);
            static::assertArrayHasKey('countryID', $state);
            static::assertArrayHasKey('name', $state);
            static::assertArrayHasKey('shortcode', $state);
            static::assertArrayHasKey('active', $state);
        }
        static::assertContains('111', array_column($country['states'], 'name'));

        // If backup data exists, restore it
        if (\is_array($existingCountryData)) {
            $existingCountryDataId = $existingCountryData['id'];
            unset($existingCountryData['id']);
            $this->connection->update('s_core_translations', $existingCountryData, ['id' => $existingCountryDataId]);
        }
        if (\is_array($existingStateData)) {
            $existingStateDataId = $existingStateData['id'];
            unset($existingStateData['id']);
            $this->connection->update('s_core_translations', $existingStateData, ['id' => $existingStateDataId]);
        }
        if (\is_array($existingGermanyData)) {
            $existingGermanyDataId = $existingGermanyData['id'];
            unset($existingGermanyData['id']);
            $this->connection->update('s_core_countries', $existingGermanyData, ['id' => $existingGermanyDataId]);
        }

        // Remove shop hack
        $context->getShop()->setIsDefault(true);
    }

    /**
     * @return array<string, mixed>
     */
    public function testsGetDownloads(): array
    {
        $customer = $this->createDummyCustomer();
        $this->session->offsetSet('sUserId', $customer->getId());

        // New customers don't have available downloads
        $downloads = $this->module->sGetDownloads();
        static::assertCount(0, $downloads['orderData']);

        // Inject demo data
        $orderData = [
            'ordernumber' => uniqid((string) rand()),
            'userID' => $customer->getId(),
            'invoice_amount' => '37.99',
            'invoice_amount_net' => 31.92,
            'invoice_shipping' => '0',
            'invoice_shipping_net' => 0.0,
            'ordertime' => '2014-03-14 10:26:20',
            'status' => 0,
            'cleared' => 17,
            'paymentID' => '4',
            'transactionID' => '',
            'comment' => '',
            'customercomment' => '',
            'internalcomment' => '',
            'net' => '0',
            'taxfree' => '0',
            'partnerID' => '',
            'temporaryID' => '',
            'referer' => '',
            'cleareddate' => null,
            'trackingcode' => '',
            'language' => '2',
            'dispatchID' => '9',
            'currency' => 'EUR',
            'currencyFactor' => '1',
            'subshopID' => '1',
            'remote_addr' => '127.0.0.1',
        ];

        $this->connection->insert('s_order', $orderData);
        $orderId = $this->connection->lastInsertId();

        $orderDetailsData = [
            'orderID' => $orderId,
            'ordernumber' => '20003',
            'articleID' => '98765',
            'articleordernumber' => 'SW10196',
            'price' => '34.99',
            'quantity' => '1',
            'name' => 'ESD download article',
            'status' => '0',
            'shipped' => '0',
            'shippedgroup' => '0',
            'releasedate' => null,
            'modus' => '0',
            'esdarticle' => '1',
            'taxID' => '1',
            'tax_rate' => '19',
            'config' => '',
        ];

        $this->connection->insert('s_order_details', $orderDetailsData);
        $orderDetailId = $this->connection->lastInsertId();

        $orderEsdData = [
            'serialID' => '8',
            'esdID' => '2',
            'userID' => $customer->getId(),
            'orderID' => $orderId,
            'orderdetailsID' => $orderDetailId,
            'datum' => '2014-03-14 10:26:20',
        ];

        $this->connection->insert('s_order_esd', $orderEsdData);

        // Mock a login
        $orderEsdId = $this->connection->lastInsertId();

        // Calling the method should now return the expected data
        $downloads = $this->module->sGetDownloads();
        $result = $downloads['orderData'];

        static::assertCount(1, $result);
        $esd = end($result);
        static::assertArrayHasKey('id', $esd);
        static::assertArrayHasKey('ordernumber', $esd);
        static::assertArrayHasKey('invoice_amount', $esd);
        static::assertArrayHasKey('invoice_amount_net', $esd);
        static::assertArrayHasKey('invoice_shipping', $esd);
        static::assertArrayHasKey('invoice_shipping_net', $esd);
        static::assertArrayHasKey('datum', $esd);
        static::assertArrayHasKey('status', $esd);
        static::assertArrayHasKey('cleared', $esd);
        static::assertArrayHasKey('comment', $esd);
        static::assertArrayHasKey('details', $esd);
        static::assertSame($orderData['ordernumber'], $esd['ordernumber']);
        static::assertSame('37,99', $esd['invoice_amount']);
        static::assertSame($orderData['invoice_amount_net'], (float) $esd['invoice_amount_net']);
        static::assertSame($orderData['invoice_shipping'], $esd['invoice_shipping']);
        static::assertSame($orderData['invoice_shipping_net'], (float) $esd['invoice_shipping_net']);
        static::assertSame('14.03.2014 10:26', $esd['datum']);
        static::assertSame($orderData['status'], (int) $esd['status']);
        static::assertSame($orderData['cleared'], (int) $esd['cleared']);
        static::assertSame($orderData['comment'], $esd['comment']);
        static::assertCount(1, $esd['details']);
        $esdDetail = end($esd['details']);

        static::assertArrayHasKey('id', $esdDetail);
        static::assertArrayHasKey('orderID', $esdDetail);
        static::assertArrayHasKey('ordernumber', $esdDetail);
        static::assertArrayHasKey('articleID', $esdDetail);
        static::assertArrayHasKey('articleordernumber', $esdDetail);
        static::assertArrayHasKey('serial', $esdDetail);
        static::assertArrayHasKey('esdLink', $esdDetail);
        static::assertNotNull($esdDetail['esdLink']);

        return [
            'customer' => $customer,
            'orderEsdId' => $orderEsdId,
            'orderDetailId' => $orderDetailId,
            'orderId' => $orderId,
            'orderData' => $orderData,
        ];
    }

    /**
     * @depends testsGetDownloads
     * @ticket SW-5653
     *
     * @param array<string, mixed> $demoData
     */
    public function testsGetOpenOrderData(array $demoData): void
    {
        $this->session->clear();

        // Inherit data from previous test
        $customer = $demoData['customer'];
        $oldOrderId = $demoData['orderId'];
        $orderEsdId = $demoData['orderEsdId'];
        $orderNumber = uniqid((string) rand());

        // Add another order to the customer
        $orderData = [
            'ordernumber' => $orderNumber,
            'userID' => $customer->getId(),
            'invoice_amount' => '16.89',
            'invoice_amount_net' => '14.2',
            'invoice_shipping' => '3.9',
            'invoice_shipping_net' => '3.28',
            'ordertime' => '2013-04-08 17:39:30',
            'status' => '0',
            'cleared' => '17',
            'paymentID' => '5',
            'transactionID' => '',
            'comment' => '',
            'customercomment' => '',
            'internalcomment' => '',
            'net' => '0',
            'taxfree' => '0',
            'partnerID' => '',
            'temporaryID' => '',
            'referer' => '',
            'cleareddate' => null,
            'trackingcode' => '',
            'language' => '2',
            'dispatchID' => '9',
            'currency' => 'EUR',
            'currencyFactor' => '1',
            'subshopID' => '1',
            'remote_addr' => '172.16.10.71',
        ];

        $this->connection->insert('s_order', $orderData);
        $orderId = $this->connection->lastInsertId();

        $this->connection->executeStatement("
            INSERT IGNORE INTO `s_order_details` (`orderID`, `ordernumber`, `articleID`, `articleordernumber`, `price`, `quantity`, `name`, `status`, `shipped`, `shippedgroup`, `releasedate`, `modus`, `esdarticle`, `taxID`, `tax_rate`, `config`) VALUES
            (?, ?, 12, 'SW10012', 9.99, 1, 'Kobra Vodka 37,5%', 0, 0, 0, NULL, 0, 0, 1, 19, ''),
            (?, ?, 0, 'SHIPPINGDISCOUNT', -2, 1, 'Warenkorbrabatt', 0, 0, 0, NULL, 4, 0, 0, 19, ''),
            (?, ?, 0, 'sw-surcharge', 5, 1, 'Mindermengenzuschlag', 0, 0, 0, NULL, 4, 0, 0, 19, '');
        ", [
            $orderId, $orderNumber,
            $orderId, $orderNumber,
            $orderId, $orderNumber,
        ]);

        // At this point, the user is not logged in so we should have no data
        $data = $this->module->sGetOpenOrderData();
        static::assertCount(0, $data['orderData']);

        // Mock a login
        $this->session->offsetSet('sUserId', $customer->getId());

        // Calling the method should now return the expected data
        $result = $this->module->sGetOpenOrderData();
        $result = $result['orderData'];

        static::assertCount(1, $result);
        foreach ($result as $order) {
            static::assertArrayHasKey('id', $order);
            static::assertArrayHasKey('ordernumber', $order);
            static::assertArrayHasKey('invoice_amount', $order);
            static::assertArrayHasKey('invoice_amount_net', $order);
            static::assertArrayHasKey('invoice_shipping', $order);
            static::assertArrayHasKey('invoice_shipping_net', $order);
            static::assertArrayHasKey('datum', $order);
            static::assertArrayHasKey('status', $order);
            static::assertArrayHasKey('cleared', $order);
            static::assertArrayHasKey('comment', $order);
            static::assertArrayHasKey('details', $order);
            foreach ($order['details'] as $detail) {
                static::assertArrayHasKey('id', $detail);
                static::assertArrayHasKey('orderID', $detail);
                static::assertArrayHasKey('ordernumber', $detail);
                static::assertArrayHasKey('articleID', $detail);
                static::assertArrayHasKey('articleordernumber', $detail);
                static::assertArrayHasKey('amountNumeric', $detail);
                static::assertArrayHasKey('priceNumeric', $detail);
            }

            // This tests SW-5653
            if ($order['id'] == $orderId) {
                static::assertNotEmpty($order);
                static::assertSame($orderNumber, $order['ordernumber']);
                static::assertSame($customer->getId(), (int) $order['userID']);
                break;
            }
        }

        $this->connection->delete('s_order_esd', ['id' => $orderEsdId]);
        $this->connection->delete('s_order_details', ['orderID' => $orderId]);
        $this->connection->delete('s_order_details', ['orderID' => $oldOrderId]);
        $this->connection->delete('s_order', ['id' => $orderId]);
        $this->connection->delete('s_order', ['id' => $oldOrderId]);
        $this->deleteDummyCustomer($customer);
        $this->session->offsetSet('sUserId', null);
    }

    public function testGetEmailAndUser(): void
    {
        $this->session->clear();

        $customer = $this->createDummyCustomer();

        // Test sGetUserMailById with null and expected cases
        static::assertNull($this->module->sGetUserMailById());
        $this->session->offsetSet('sUserId', $customer->getId());
        static::assertSame($customer->getEmail(), $this->module->sGetUserMailById());

        // Test sGetUserByMail with null and expected cases
        static::assertNull($this->module->sGetUserByMail(uniqid((string) rand())));
        static::assertSame($customer->getId(), $this->module->sGetUserByMail($customer->getEmail()));

        // Test sGetUserNameById with null and expected cases
        static::assertEmpty($this->module->sGetUserNameById(random_int(10000, PHP_INT_MAX)));
        static::assertSame(
            ['firstname' => 'Max', 'lastname' => 'Mustermann'],
            $this->module->sGetUserNameById($customer->getId())
        );

        $this->deleteDummyCustomer($customer);
        $this->session->offsetSet('sUserId', null);
    }

    public function testsGetUserDataWithoutLogin(): void
    {
        $this->session->clear();

        static::assertSame(
            ['additional' => [
                    'country' => [],
                    'countryShipping' => [],
                    'stateShipping' => ['id' => 0],
                ],
            ],
            $this->module->sGetUserData()
        );

        $this->session->offsetSet('sCountry', 20);

        static::assertEquals(
            ['additional' => [
                    'country' => [
                        'id' => '20',
                        'countryname' => 'Namibia',
                        'countryiso' => 'NA',
                        'areaID' => '2',
                        'countryen' => 'NAMIBIA',
                        'position' => '10',
                        'notice' => '',
                        'taxfree' => '0',
                        'taxfree_ustid' => '0',
                        'taxfree_ustid_checked' => '0',
                        'active' => '0',
                        'iso3' => 'NAM',
                        'display_state_in_registration' => '0',
                        'force_state_in_registration' => '0',
                        'countryarea' => 'welt',
                        'allow_shipping' => '1',
                    ],
                    'countryShipping' => [
                        'id' => '20',
                        'countryname' => 'Namibia',
                        'countryiso' => 'NA',
                        'areaID' => '2',
                        'countryen' => 'NAMIBIA',
                        'position' => '10',
                        'notice' => '',
                        'taxfree' => '0',
                        'taxfree_ustid' => '0',
                        'taxfree_ustid_checked' => '0',
                        'active' => '0',
                        'iso3' => 'NAM',
                        'display_state_in_registration' => '0',
                        'force_state_in_registration' => '0',
                        'countryarea' => 'welt',
                        'allow_shipping' => '1',
                    ],
                    'stateShipping' => ['id' => 0],
                ],
            ],
            $this->module->sGetUserData()
        );
    }

    public function testsGetUserDataWithLogin(): void
    {
        $customer = $this->createDummyCustomer();
        $this->session->offsetSet('sUserId', $customer->getId());
        $this->session->offsetUnset('sState');

        $result = $this->module->sGetUserData();
        static::assertIsArray($result);

        $expectedData = [
            'billingaddress' => [
                'company' => '',
                'department' => '',
                'salutation' => 'mr',
                'firstname' => 'Max',
                'lastname' => 'Mustermann',
                'street' => 'Kraftweg, 22',
                'zipcode' => '12345',
                'city' => 'Musterhausen',
                'phone' => '',
                'countryID' => '2',
                'stateID' => null,
                'ustid' => '',
                'title' => null,
                'additional_address_line1' => 'IT-Department',
                'additional_address_line2' => 'Second Floor',
                'attributes' => [
                    'text1' => 'Freitext1',
                    'text2' => 'Freitext2',
                    'text3' => null,
                    'text4' => null,
                    'text5' => null,
                    'text6' => null,
                ],
            ],
            'additional' => [
                'country' => [
                    'countryname' => 'Deutschland',
                    'countryiso' => 'DE',
                    'areaID' => '1',
                    'countryen' => 'GERMANY',
                    'position' => '1',
                    'notice' => '',
                    'taxfree' => '0',
                    'taxfree_ustid' => '0',
                    'taxfree_ustid_checked' => '0',
                    'active' => '1',
                    'iso3' => 'DEU',
                    'display_state_in_registration' => '0',
                    'force_state_in_registration' => '0',
                    'countryarea' => 'deutschland',
                ],
                'state' => [],
                'user' => [
                    'password' => $customer->getPassword(),
                    'encoder' => 'bcrypt',
                    'email' => $customer->getEmail(),
                    'active' => '1',
                    'accountmode' => '0',
                    'confirmationkey' => '',
                    'paymentID' => 5,
                    'customernumber' => $customer->getNumber(),
                    'firstlogin' => $customer->getFirstLogin() ? $customer->getFirstLogin()->format('Y-m-d') : null,
                    'lastlogin' => $customer->getLastLogin() ? $customer->getLastLogin()->format('Y-m-d H:i:s') : null,
                    'sessionID' => '',
                    'newsletter' => 0,
                    'validation' => '',
                    'affiliate' => '0',
                    'customergroup' => 'EK',
                    'paymentpreset' => '0',
                    'language' => '1',
                    'subshopID' => '1',
                    'referer' => '',
                    'pricegroupID' => null,
                    'internalcomment' => '',
                    'failedlogins' => '0',
                    'lockeduntil' => null,
                    'default_billing_address_id' => $customer->getDefaultBillingAddress() ? $customer->getDefaultBillingAddress()->getId() : null,
                    'default_shipping_address_id' => $customer->getDefaultShippingAddress() ? $customer->getDefaultShippingAddress()->getId() : null,
                    'birthday' => '1986-12-20',
                    'firstname' => 'Max',
                    'lastname' => 'Mustermann',
                    'salutation' => 'mr',
                    'title' => null,
                ],
                'countryShipping' => [
                    'countryname' => 'Australien',
                    'countryiso' => 'AU',
                    'areaID' => '2',
                    'countryen' => 'AUSTRALIA',
                    'position' => '10',
                    'notice' => '',
                    'taxfree' => '0',
                    'taxfree_ustid' => '0',
                    'taxfree_ustid_checked' => '0',
                    'active' => '1',
                    'iso3' => 'AUS',
                    'display_state_in_registration' => '0',
                    'force_state_in_registration' => '0',
                    'countryarea' => 'welt',
                ],
                'stateShipping' => [],
                'payment' => [
                    'name' => 'prepayment',
                    'description' => 'Vorkasse',
                    'template' => 'prepayment.tpl',
                    'class' => 'prepayment.php',
                    'table' => '',
                    'hide' => '0',
                    'additionaldescription' => 'Sie zahlen einfach vorab und erhalten die Ware bequem und günstig bei Zahlungseingang nach Hause geliefert.',
                    'debit_percent' => '0',
                    'surcharge' => '0',
                    'surchargestring' => '',
                    'position' => '1',
                    'active' => '1',
                    'esdactive' => '0',
                    'mobile_inactive' => '0',
                    'embediframe' => '',
                    'hideprospect' => '0',
                    'action' => null,
                    'pluginID' => null,
                    'source' => null,
                ],
            ],
            'shippingaddress' => [
                'company' => 'Widgets Inc.',
                'department' => '',
                'salutation' => 'Mr',
                'firstname' => 'Max',
                'lastname' => 'Mustermann',
                'street' => 'Merkel Strasse, 10',
                'zipcode' => '98765',
                'city' => 'Musterhausen',
                'countryID' => '4',
                'stateID' => null,
                'title' => null,
                'additional_address_line1' => 'Sales-Department',
                'additional_address_line2' => 'Third Floor',
                'attributes' => [
                    'text1' => 'Freitext1',
                    'text2' => 'Freitext2',
                    'text3' => null,
                    'text4' => null,
                    'text5' => null,
                    'text6' => null,
                ],
            ],
        ];

        $this->assertArray($expectedData, $result);

        $this->deleteDummyCustomer($customer);
        $this->session->offsetSet('sUserId', null);
    }

    public function testsManageRisks(): void
    {
        $customerModel = $this->createDummyCustomer();
        $this->session->offsetSet('sUserId', $customerModel->getId());

        $basket = [
            'content' => 1,
            'AmountNumeric' => 10,
        ];
        $customerData = $this->module->sGetUserData();
        static::assertIsArray($customerData);

        $date = new DateTime();

        // Inject demo data
        $orderData = [
            'ordernumber' => uniqid((string) rand()),
            'userID' => $customerModel->getId(),
            'invoice_amount' => '37.99',
            'invoice_amount_net' => '31.92',
            'invoice_shipping' => '0',
            'invoice_shipping_net' => '0',
            'ordertime' => $date->format('Y-m-d H:i:s'),
            'status' => '0',
            'paymentID' => '4',
            'transactionID' => '',
            'comment' => '',
            'customercomment' => '',
            'internalcomment' => '',
            'net' => '0',
            'taxfree' => '0',
            'partnerID' => '',
            'temporaryID' => '',
            'referer' => '',
            'cleareddate' => null,
            'cleared' => 16,
            'trackingcode' => '',
            'language' => '2',
            'dispatchID' => '9',
            'currency' => 'EUR',
            'currencyFactor' => '1',
            'subshopID' => '1',
            'remote_addr' => '127.0.0.1',
        ];

        $this->connection->insert('s_order', $orderData);
        $orderId = $this->connection->lastInsertId();

        // No rules, returns false
        static::assertFalse($this->module->sManageRisks(2, $basket, $customerData));

        // Test all rules

        // sRiskORDERVALUELESS
        $this->connection->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'ORDERVALUELESS',
                'value1' => 20,
            ]
        );
        $firstTestRuleId = $this->connection->lastInsertId();
        static::assertTrue($this->module->sManageRisks(2, $basket, $customerData));

        // sRiskORDERVALUEMORE
        $this->connection->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'ORDERVALUEMORE',
                'value1' => 20,
            ]
        );
        // Test 'OR' logic between different rules (only one needs to be true)
        static::assertTrue($this->module->sManageRisks(2, $basket, $customerData));

        // Deleting the first rule, only a false one is left
        $this->connection->delete('s_core_rulesets', ['id' => $firstTestRuleId]);
        static::assertFalse($this->module->sManageRisks(2, $basket, $customerData));

        // sRiskCUSTOMERGROUPIS
        // sRiskCUSTOMERGROUPISNOT
        $this->connection->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'CUSTOMERGROUPIS',
                'value1' => 'EK',
                'rule2' => 'CUSTOMERGROUPISNOT',
                'value2' => 'EK',
            ]
        );

        // Test 'AND' logic between the two parts of the same rule (both need to be true)
        static::assertFalse($this->module->sManageRisks(2, $basket, $customerData));

        // sRiskZIPCODE
        $this->connection->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'ZIPCODE',
                'value1' => '98765',
            ]
        );
        static::assertTrue($this->module->sManageRisks(2, $basket, $customerData));

        // sRiskBILLINGZIPCODE
        $this->connection->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'BILLINGZIPCODE',
                'value1' => '12345',
            ]
        );

        static::assertTrue($this->module->sManageRisks(2, $basket, $customerData));
        $this->connection->createQueryBuilder()->delete('s_core_rulesets')->where('id >= ' . $firstTestRuleId)->execute();

        // sRiskZONEIS
        $this->connection->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'ZONEIS',
                'value1' => '12345',
            ]
        );
        static::assertFalse($this->module->sManageRisks(2, $basket, $customerData));

        // sRiskZONEISNOT
        $this->connection->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'ZONEISNOT',
                'value1' => '12345',
            ]
        );
        static::assertTrue($this->module->sManageRisks(2, $basket, $customerData));

        // sRiskLANDIS
        // sRiskLANDISNOT
        $this->connection->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'LANDIS',
                'value1' => 'AU',
                'rule2' => 'LANDISNOT',
                'value2' => 'UK',
            ]
        );
        static::assertTrue($this->module->sManageRisks(2, $basket, $customerData));

        // sRiskBILLINGLANDIS
        // sRiskBILLINGLANDISNOT
        $this->connection->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'BILLINGLANDIS',
                'value1' => 'DE',
                'rule2' => 'BILLINGLANDISNOT',
                'value2' => 'UK',
            ]
        );
        static::assertTrue($this->module->sManageRisks(2, $basket, $customerData));

        // sRiskNEWCUSTOMER
        $this->connection->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'NEWCUSTOMER',
            ]
        );
        static::assertTrue($this->module->sManageRisks(2, $basket, $customerData));
        $this->connection->createQueryBuilder()->delete('s_core_rulesets')->where('id >= ' . $firstTestRuleId)->execute();

        // sRiskORDERPOSITIONSMORE
        $this->connection->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'ORDERPOSITIONSMORE',
                'value1' => '2',
            ]
        );
        static::assertFalse($this->module->sManageRisks(2, $basket, $customerData));

        $this->generateBasketSession();
        $this->basketModule->sAddArticle('SW10118.8');

        // sRiskATTRIS
        $this->connection->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'ATTRIS',
                'value1' => '1|0',
            ]
        );

        $fullBasket = $this->basketModule->sGetBasket();
        static::assertTrue($this->module->sManageRisks(2, $fullBasket, $customerData));
        $this->connection->createQueryBuilder()->delete('s_core_rulesets')->where('id >= ' . $firstTestRuleId)->execute();

        $this->basketModule->sAddArticle('SW10118.8');
        // sRiskATTRISNOT
        $this->connection->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'ATTRISNOT',
                'value1' => '17|null',
            ]
        );
        static::assertFalse($this->module->sManageRisks(2, $fullBasket, $customerData));

        // sRiskDUNNINGLEVELONE
        $this->connection->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'DUNNINGLEVELONE',
            ]
        );
        static::assertFalse($this->module->sManageRisks(2, $fullBasket, $customerData));

        // sRiskDUNNINGLEVELTWO
        $this->connection->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'DUNNINGLEVELTWO',
            ]
        );
        static::assertFalse($this->module->sManageRisks(2, $fullBasket, $customerData));

        // sRiskDUNNINGLEVELTHREE
        $this->connection->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'DUNNINGLEVELTHREE',
            ]
        );
        static::assertFalse($this->module->sManageRisks(2, $fullBasket, $customerData));

        // sRiskINKASSO
        $this->connection->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'INKASSO',
            ]
        );
        static::assertTrue($this->module->sManageRisks(2, $fullBasket, $customerData));

        // sRiskLASTORDERLESS
        $this->connection->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'LASTORDERLESS',
                'value1' => '1',
            ]
        );
        static::assertTrue($this->module->sManageRisks(2, $fullBasket, $customerData));
        $this->connection->createQueryBuilder()->delete('s_core_rulesets')->where('id >= ' . $firstTestRuleId)->execute();

        // sRiskARTICLESFROM
        $this->connection->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'ARTICLESFROM',
                'value1' => '1',
            ]
        );
        static::assertFalse($this->module->sManageRisks(2, $fullBasket, $customerData));

        // sRiskARTICLESFROM
        $this->connection->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'ARTICLESFROM',
                'value1' => '9',
            ]
        );
        static::assertTrue($this->module->sManageRisks(2, $fullBasket, $customerData));

        // sRiskLASTORDERSLESS
        $this->connection->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'LASTORDERSLESS',
                'value1' => '9',
            ]
        );
        static::assertTrue($this->module->sManageRisks(2, $fullBasket, $customerData));
        $this->connection->createQueryBuilder()->delete('s_core_rulesets')->where('id >= ' . $firstTestRuleId)->execute();

        // sRiskLASTORDERSLESS
        $this->connection->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'LASTORDERSLESS',
                'value1' => '0',
            ]
        );
        static::assertFalse($this->module->sManageRisks(2, $fullBasket, $customerData));

        // sRiskPREGSTREET
        $this->connection->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'PREGSTREET',
                'value1' => 'Merkel',
            ]
        );
        static::assertTrue($this->module->sManageRisks(2, $fullBasket, $customerData));
        $this->connection->createQueryBuilder()->delete('s_core_rulesets')->where('id >= ' . $firstTestRuleId)->execute();

        // sRiskPREGSTREET
        $this->connection->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'PREGSTREET',
                'value1' => 'Google',
            ]
        );
        static::assertFalse($this->module->sManageRisks(2, $fullBasket, $customerData));

        // sRiskPREGBILLINGSTREET
        $this->connection->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'PREGBILLINGSTREET',
                'value1' => 'Google',
            ]
        );
        static::assertFalse($this->module->sManageRisks(2, $fullBasket, $customerData));

        // sRiskDIFFER
        $this->connection->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'DIFFER',
            ]
        );
        static::assertTrue($this->module->sManageRisks(2, $fullBasket, $customerData));

        // sRiskCUSTOMERNR
        $this->connection->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'CUSTOMERNR',
                'value1' => $customerModel->getNumber(),
            ]
        );
        static::assertTrue($this->module->sManageRisks(2, $fullBasket, $customerData));
        $this->connection->createQueryBuilder()->delete('s_core_rulesets')->where('id >= ' . $firstTestRuleId)->execute();

        // sRiskCUSTOMERNR
        $this->connection->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'CUSTOMERNR',
                'value1' => 'ThisIsNeverGoingToBeACustomerNumber',
            ]
        );
        static::assertFalse($this->module->sManageRisks(2, $fullBasket, $customerData));

        // sRiskLASTNAME
        $this->connection->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'LASTNAME',
                'value1' => 'Mustermann',
            ]
        );
        static::assertTrue($this->module->sManageRisks(2, $fullBasket, $customerData));
        $this->connection->createQueryBuilder()->delete('s_core_rulesets')->where('id >= ' . $firstTestRuleId)->execute();

        // sRiskLASTNAME
        $this->connection->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'LASTNAME',
                'value1' => 'NotMustermann',
            ]
        );
        static::assertFalse($this->module->sManageRisks(2, $fullBasket, $customerData));

        // sRiskSUBSHOP
        $this->connection->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'SUBSHOP',
                'value1' => '1',
            ]
        );
        static::assertTrue($this->module->sManageRisks(2, $fullBasket, $customerData));
        $this->connection->createQueryBuilder()->delete('s_core_rulesets')->where('id >= ' . $firstTestRuleId)->execute();

        // sRiskSUBSHOP
        $this->connection->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'SUBSHOP',
                'value1' => '2',
            ]
        );
        static::assertFalse($this->module->sManageRisks(2, $fullBasket, $customerData));

        // sRiskSUBSHOPNOT
        $this->connection->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'SUBSHOPNOT',
                'value1' => '2',
            ]
        );
        static::assertTrue($this->module->sManageRisks(2, $fullBasket, $customerData));
        $this->connection->createQueryBuilder()->delete('s_core_rulesets')->where('id >= ' . $firstTestRuleId)->execute();

        // sRiskSUBSHOPNOT
        $this->connection->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'SUBSHOPNOT',
                'value1' => '1',
            ]
        );
        static::assertFalse($this->module->sManageRisks(2, $fullBasket, $customerData));

        // sRiskCURRENCIESISOIS
        $this->connection->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'CURRENCIESISOIS',
                'value1' => 'eur',
            ]
        );
        static::assertTrue($this->module->sManageRisks(2, $fullBasket, $customerData));
        $this->connection->createQueryBuilder()->delete('s_core_rulesets')->where('id >= ' . $firstTestRuleId)->execute();

        // sRiskCURRENCIESISOIS
        $this->connection->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'CURRENCIESISOIS',
                'value1' => 'yen',
            ]
        );
        static::assertFalse($this->module->sManageRisks(2, $fullBasket, $customerData));

        // sRiskCURRENCIESISOISNOT
        $this->connection->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'CURRENCIESISOISNOT',
                'value1' => 'eur',
            ]
        );
        static::assertFalse($this->module->sManageRisks(2, $fullBasket, $customerData));

        // sRiskCURRENCIESISOISNOT
        $this->connection->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'CURRENCIESISOISNOT',
                'value1' => 'yen',
            ]
        );
        static::assertTrue($this->module->sManageRisks(2, $fullBasket, $customerData));

        $this->connection->delete('s_order', ['id' => $orderId]);
        $this->deleteDummyCustomer($customerModel);
        $this->session->offsetSet('sUserId', null);
    }

    public function testsNewsletterSubscriptionWithPostData(): void
    {
        // Test subscribe with empty post field and empty address, fail validation
        $this->getRequest()->setPost('newsletter', '');
        $result = $this->module->sNewsletterSubscription('');

        static::assertArrayHasKey('code', $result);
        static::assertArrayHasKey('message', $result);
        static::assertArrayHasKey('sErrorFlag', $result);
        static::assertSame(5, $result['code']);
        $message = $this->snippetManager->getNamespace('frontend/account/internalMessages')
            ->get('ErrorFillIn', 'Please fill in all red fields');
        static::assertSame($message, $result['message']);
        static::assertSame(['newsletter' => true], $result['sErrorFlag']);
    }

    public function testsNewsletterSubscription(): void
    {
        $validAddress = uniqid((string) rand()) . '@shopware.com';

        // Test unsubscribe with non existing email, fail
        $result = $this->module->sNewsletterSubscription(uniqid((string) rand()) . '@shopware.com', true);
        static::assertSame(
            [
                'code' => 4,
                'message' => $this->snippetManager->getNamespace('frontend/account/internalMessages')
                        ->get('NewsletterFailureNotFound', 'This mail address could not be found'),
            ],
            $result
        );

        // Test unsubscribe with empty post field, fail validation
        $result = $this->module->sNewsletterSubscription('', true);
        static::assertSame(
            [
                'code' => 6,
                'message' => $this->snippetManager->getNamespace('frontend/account/internalMessages')
                        ->get('NewsletterFailureMail', 'Enter eMail address'),
            ],
            $result
        );

        // Test with empty field, fail validation
        $result = $this->module->sNewsletterSubscription('');
        static::assertSame(
            [
                'code' => 6,
                'message' => $this->snippetManager->getNamespace('frontend/account/internalMessages')
                        ->get('NewsletterFailureMail', 'Enter eMail address'),
            ],
            $result
        );

        // Test with malformed email, fail validation
        $result = $this->module->sNewsletterSubscription('thisIsNotAValidEmailAddress');
        static::assertSame(
            [
                'code' => 1,
                'message' => $this->snippetManager->getNamespace('frontend/account/internalMessages')
                        ->get('NewsletterFailureInvalid', 'Enter valid eMail address'),
            ],
            $result
        );

        // Check that test email does not exist
        static::assertFalse(
            $this->connection->fetchAssociative(
                'SELECT email, groupID FROM s_campaigns_mailaddresses WHERE email LIKE ?',
                [$validAddress]
            )
        );

        // Test with correct unique email, all ok
        $result = $this->module->sNewsletterSubscription($validAddress);
        static::assertSame(
            [
                'code' => 3,
                'message' => $this->snippetManager->getNamespace('frontend/account/internalMessages')
                        ->get('NewsletterSuccess', 'Thank you for receiving our newsletter'),
                'isNewRegistration' => true,
            ],
            $result
        );

        // Check that test email was inserted
        static::assertEquals(
            [
                'email' => $validAddress,
                'groupID' => $this->config->get('sNEWSLETTERDEFAULTGROUP'),
            ],
            $this->connection->fetchAssociative(
                'SELECT email, groupID FROM s_campaigns_mailaddresses WHERE email LIKE ?',
                [$validAddress]
            )
        );
        static::assertEquals(
            [
                [
                    'email' => $validAddress,
                    'groupID' => $this->config->get('sNEWSLETTERDEFAULTGROUP'),
                ],
            ],
            $this->connection->fetchAllAssociative(
                'SELECT email, groupID FROM s_campaigns_maildata WHERE email LIKE ?',
                [$validAddress]
            )
        );

        // Test with same email, fail
        $result = $this->module->sNewsletterSubscription($validAddress);
        static::assertSame(
            [
                'code' => 3,
                'message' => $this->snippetManager->getNamespace('frontend/account/internalMessages')
                        ->get('NewsletterSuccess', 'Thank you! We have entered your address.'),
                'isNewRegistration' => false,
            ],
            $result
        );

        // Test with same email in a different list, fail
        $groupId = rand(1, 9999);
        $result = $this->module->sNewsletterSubscription($validAddress, false, $groupId);
        static::assertSame(
            [
                'code' => 3,
                'message' => $this->snippetManager->getNamespace('frontend/account/internalMessages')
                        ->get('NewsletterSuccess', 'Thank you! We have entered your address.'),
                'isNewRegistration' => false,
            ],
            $result
        );

        // Check that test email address is still there, but now in two groups
        static::assertEquals(
            [
                [
                    'email' => $validAddress,
                    'groupID' => $this->config->get('sNEWSLETTERDEFAULTGROUP'),
                ],
            ],
            $this->connection->fetchAllAssociative(
                'SELECT email, groupID FROM s_campaigns_mailaddresses WHERE email LIKE ?',
                [$validAddress]
            )
        );
        static::assertEquals(
            [
                [
                    'email' => $validAddress,
                    'groupID' => $this->config->get('sNEWSLETTERDEFAULTGROUP'),
                ],
                [
                    'email' => $validAddress,
                    'groupID' => $groupId,
                ],
            ],
            $this->connection->fetchAllAssociative(
                'SELECT email, groupID FROM s_campaigns_maildata WHERE email LIKE ?',
                [$validAddress]
            )
        );

        // Test unsubscribe the same email, all ok
        $result = $this->module->sNewsletterSubscription($validAddress, true);
        static::assertSame(
            [
                'code' => 5,
                'message' => $this->snippetManager->getNamespace('frontend/account/internalMessages')
                        ->get('NewsletterMailDeleted', 'Your mail address was deleted'),
            ],
            $result
        );

        // Check that test email address was removed
        static::assertFalse(
            $this->connection->fetchAssociative(
                'SELECT email, groupID FROM s_campaigns_mailaddresses WHERE email LIKE ?',
                [$validAddress]
            )
        );

        // But not completely from maildata
        static::assertEquals(
            [
                [
                    'email' => $validAddress,
                    'groupID' => $groupId,
                ],
            ],
            $this->connection->fetchAllAssociative(
                'SELECT email, groupID FROM s_campaigns_maildata WHERE email LIKE ?',
                [$validAddress]
            )
        );

        $this->connection->delete(
            's_campaigns_maildata',
            ['email' => $validAddress]
        );
    }

    public function testsGetCountry(): void
    {
        // Empty argument, return false
        static::assertFalse($this->module->sGetCountry(''));

        // No matching country, return empty array
        static::assertSame([], $this->module->sGetCountry(-1));

        // Valid country returns valid data
        $result = $this->module->sGetCountry('de');
        static::assertIsArray($result);
        static::assertEquals(
            [
                'id' => '2',
                'countryID' => '2',
                'countryname' => 'Deutschland',
                'countryiso' => 'DE',
                'countryarea' => 'deutschland',
                'countryen' => 'GERMANY',
                'position' => '1',
                'notice' => '',
            ],
            $result
        );

        // Fetching for id or iso code gives the same result
        static::assertSame(
            $this->module->sGetCountry($result['id']),
            $result
        );
    }

    public function testsGetPaymentmean(): void
    {
        // Empty argument, return false
        static::assertFalse($this->module->sGetPaymentMean(''));

        // No matching payment mean, return empty array
        static::assertSame(['country_surcharge' => []], $this->module->sGetPaymentMean(-1));

        // Valid country returns valid data
        $result = $this->module->sGetPaymentMean(
            $this->connection->fetchOne('SELECT id FROM s_core_paymentmeans WHERE name = "prepayment"')
        );
        static::assertIsArray($result);

        static::assertEquals(
            [
                'id' => '5',
                'name' => 'prepayment',
                'description' => 'Vorkasse',
                'template' => 'prepayment.tpl',
                'class' => 'prepayment.php',
                'table' => '',
                'hide' => '0',
                'additionaldescription' => 'Sie zahlen einfach vorab und erhalten die Ware bequem und günstig bei Zahlungseingang nach Hause geliefert.',
                'debit_percent' => '0',
                'surcharge' => '0',
                'surchargestring' => '',
                'position' => '1',
                'active' => '1',
                'esdactive' => '0',
                'mobile_inactive' => '0',
                'embediframe' => '',
                'hideprospect' => '0',
                'action' => null,
                'pluginID' => null,
                'source' => null,
                'country_surcharge' => [
                    ],
            ],
            $result
        );

        // Fetching for id or iso code gives the same result
        static::assertSame(
            $this->module->sGetPaymentMean($result['name']),
            $result
        );
    }

    public function testsGetDispatchBasket(): void
    {
        $this->session->clear();

        $this->basketModule->sDeleteBasket();
        // No basket, return false
        static::assertFalse($this->module->sGetDispatchBasket());

        $this->generateBasketSession();
        $this->basketModule->sAddArticle('SW10118.8');

        // With the correct data, return properly formatted array
        // This is a big query function
        $result = $this->module->sGetDispatchBasket();
        static::assertIsArray($result);
        static::assertArrayHasKey('instock', $result);
        static::assertArrayHasKey('stockmin', $result);
        static::assertArrayHasKey('laststock', $result);
        static::assertArrayHasKey('weight', $result);
        static::assertArrayHasKey('count_article', $result);
        static::assertArrayHasKey('shippingfree', $result);
        static::assertArrayHasKey('amount', $result);
        static::assertArrayHasKey('amount_net', $result);
        static::assertArrayHasKey('amount_display', $result);
        static::assertArrayHasKey('length', $result);
        static::assertArrayHasKey('height', $result);
        static::assertArrayHasKey('width', $result);
        static::assertArrayHasKey('userID', $result);
        static::assertArrayHasKey('has_topseller', $result);
        static::assertArrayHasKey('has_comment', $result);
        static::assertArrayHasKey('has_esd', $result);
        static::assertArrayHasKey('max_tax', $result);
        static::assertArrayHasKey('basketStateId', $result);
        static::assertArrayHasKey('countryID', $result);
        static::assertArrayHasKey('paymentID', $result);
        static::assertArrayHasKey('customergroupID', $result);
        static::assertArrayHasKey('multishopID', $result);
        static::assertArrayHasKey('sessionID', $result);
    }

    public function testsGetPremiumDispatches(): void
    {
        $this->session->clear();

        // No basket, return empty array,
        static::assertSame([], $this->module->sGetPremiumDispatches());

        $this->generateBasketSession();
        $this->basketModule->sAddArticle('SW10118.8');

        $result = $this->module->sGetPremiumDispatches();

        static::assertGreaterThan(0, \count($result));
        foreach ($result as $dispatch) {
            static::assertArrayHasKey('id', $dispatch);
            static::assertArrayHasKey('name', $dispatch);
            static::assertArrayHasKey('description', $dispatch);
            static::assertArrayHasKey('calculation', $dispatch);
            static::assertArrayHasKey('status_link', $dispatch);
        }
    }

    public function testsGetPremiumDispatchSurcharge(): void
    {
        $this->session->clear();

        // No basket, return false,
        static::assertFalse($this->module->sGetPremiumDispatchSurcharge([]));

        $this->generateBasketSession();
        $this->basketModule->sAddArticle('SW10010');
        $fullBasket = $this->module->sGetDispatchBasket();
        static::assertIsArray($fullBasket);

        $result = $this->module->sGetPremiumDispatchSurcharge($fullBasket);
        static::assertSame(0.0, $result);
    }

    public function testsGetPremiumShippingcosts(): void
    {
        // No basket, return false,
        static::assertFalse($this->module->sGetPremiumShippingcosts());

        $germany = null;
        foreach ($this->module->sGetCountryList() as $country) {
            if ($country['countryiso'] === 'DE') {
                $germany = $country;
                break;
            }
        }
        static::assertIsArray($germany);

        $this->generateBasketSession();
        $this->basketModule->sAddArticle('SW10010');

        // With country data, no dispatch method
        static::assertSame(
            sAdmin::NO_SHIPPING_COSTS,
            $this->module->sGetPremiumShippingcosts($germany)
        );

        // With dispatch method
        $this->session->offsetSet('sDispatch', self::DEFAULT_SHIPPING_METHOD_ID);
        $result = $this->module->sGetPremiumShippingcosts($germany);
        static::assertIsArray($result);
        static::assertArrayHasKey('brutto', $result);
        static::assertArrayHasKey('netto', $result);
        static::assertArrayHasKey('value', $result);
        static::assertArrayHasKey('factor', $result);
        static::assertArrayHasKey('surcharge', $result);
        static::assertArrayHasKey('tax', $result);
    }

    public function testsGetPremiumShippingcostsWithCountryTaxRule(): void
    {
        $austria = null;
        foreach ($this->module->sGetCountryList() as $country) {
            if ($country['countryiso'] === 'AT') {
                $austria = $country;
                break;
            }
        }
        static::assertIsArray($austria);

        $expectedTaxValue = 20.0;

        $newTax = new Tax();
        $newTax->setTax('19.0');
        $newTax->setName('Test tax');
        $taxRule = new Rule();
        $taxRule->setTax((string) $expectedTaxValue);
        $taxRule->setName('Test tax austria');
        $taxRule->setActive(true);
        $taxRule->setCountryId($austria['id']);
        $taxRule->setAreaId($austria['areaId']);
        $taxRule->setGroup($newTax);
        $taxRule->setCustomerGroupId(self::DEFAULT_CUSTOMER_GROUP_ID);
        $newTax->setRules(new ArrayCollection([$taxRule]));

        $modelManager = $this->getContainer()->get(ModelManager::class);
        $modelManager->persist($newTax);
        $modelManager->flush($newTax);
        $modelManager->refresh($newTax);

        $dispatch = $modelManager->find(Dispatch::class, self::DEFAULT_SHIPPING_METHOD_ID);
        static::assertInstanceOf(Dispatch::class, $dispatch);
        $dispatch->setTaxCalculation($newTax->getId());
        $modelManager->persist($dispatch);
        $modelManager->flush($dispatch);

        $this->session->offsetSet('sCountry', $austria['id']);
        $this->session->offsetSet('sArea', $austria['areaId']);
        $this->session->offsetSet('sDispatch', self::DEFAULT_SHIPPING_METHOD_ID);
        $this->getContainer()->get(ContextServiceInterface::class)->initializeShopContext();

        $this->generateBasketSession();
        $this->basketModule->sAddArticle('SW10010');

        $result = $this->module->sGetPremiumShippingcosts($austria);
        static::assertIsArray($result);
        static::assertSame($newTax->getId(), (int) $result['taxMode']);
        static::assertSame($expectedTaxValue, $result['tax']);
    }

    /**
     * @param array<string, array<string, mixed>> $userData
     * @dataProvider dataProviderDataCustomerAttributeIsNot
     */
    public function testCustomerAttributeIsNot(array $userData, string $attribute, bool $expectation): void
    {
        static::assertSame($expectation, $this->module->sRiskCUSTOMERATTRISNOT($userData, [], $attribute));
    }

    public function dataProviderDataCustomerAttributeIsNot(): Generator
    {
        // Not logged in
        yield [
            [
                'additional' => [],
            ],
            'attr1|1',
            true,
        ];

        // Wrong configured
        yield [
            [
                'additional' => [
                    'user' => [],
                ],
            ],
            'attr1',
            true,
        ];

        // Attribute not existing
        yield [
            [
                'additional' => [
                    'user' => [
                    ],
                ],
            ],
            'attr1|1',
            true,
        ];

        // Not Matching
        yield [
            [
                'additional' => [
                    'user' => [
                        'attr1' => '1',
                    ],
                ],
            ],
            'attr1|1',
            false,
        ];

        // Matching
        yield [
            [
                'additional' => [
                    'user' => [
                        'attr1' => '2',
                    ],
                ],
            ],
            'attr1|1',
            true,
        ];
    }

    private function generateBasketSession(): void
    {
        $this->session->offsetSet('sessionId', Random::getAlphanumericString(32));
    }

    /**
     * @param mixed[] $expected
     * @param mixed[] $actual
     */
    private function assertArray(array $expected, array $actual): void
    {
        foreach ($expected as $key => $value) {
            static::assertArrayHasKey($key, $actual);
            $currentActual = $actual[$key];

            if (\is_array($value)) {
                $this->assertArray($value, $currentActual);
            } else {
                static::assertEquals($value, $currentActual);
            }
        }
    }

    /**
     * Create dummy customer entity
     */
    private function createDummyCustomer(): Customer
    {
        $date = new DateTime();
        $date->modify('-8 days');
        $lastLogin = $date->format(DateTime::ISO8601);

        $oldDate = DateTime::createFromFormat('Y-m-d', '1986-12-20');
        static::assertNotFalse($oldDate);
        $birthday = $oldDate->format(DateTime::ISO8601);

        $testData = [
            'password' => 'fooobar',
            'email' => uniqid((string) rand()) . 'test@foobar.com',
            'customernumber' => 'dummy customer number',
            'lastlogin' => $lastLogin,

            'salutation' => 'mr',
            'firstname' => 'Max',
            'lastname' => 'Mustermann',
            'birthday' => $birthday,

            'billing' => [
                'salutation' => 'mr',
                'firstname' => 'Max',
                'lastname' => 'Mustermann',
                'attribute' => [
                    'text1' => 'Freitext1',
                    'text2' => 'Freitext2',
                ],
                'zipcode' => '12345',
                'city' => 'Musterhausen',
                'street' => 'Kraftweg, 22',
                'country' => '2',
                'additionalAddressLine1' => 'IT-Department',
                'additionalAddressLine2' => 'Second Floor',
            ],

            'shipping' => [
                'salutation' => 'Mr',
                'company' => 'Widgets Inc.',
                'firstname' => 'Max',
                'lastname' => 'Mustermann',
                'zipcode' => '98765',
                'city' => 'Musterhausen',
                'street' => 'Merkel Strasse, 10',
                'country' => '4',
                'attribute' => [
                    'text1' => 'Freitext1',
                    'text2' => 'Freitext2',
                ],
                'additionalAddressLine1' => 'Sales-Department',
                'additionalAddressLine2' => 'Third Floor',
            ],

            'debit' => [
                'account' => 'Fake Account',
                'bankCode' => '55555555',
                'bankName' => 'Fake Bank',
                'accountHolder' => 'Max Mustermann',
            ],
        ];

        $customerResource = new CustomerResource();
        $customerResource->setManager($this->getContainer()->get(ModelManager::class));

        return $customerResource->create($testData);
    }

    /**
     * Deletes all dummy customer entity
     */
    private function deleteDummyCustomer(Customer $customer): void
    {
        $this->connection->delete('s_user_addresses', ['user_id' => $customer->getId()]);
        $this->connection->delete('s_core_payment_data', ['user_id' => $customer->getId()]);
        $this->connection->delete('s_user_attributes', ['userID' => $customer->getId()]);
        $this->connection->delete('s_user', ['id' => $customer->getId()]);
    }

    private function getRequest(): Enlight_Controller_Request_Request
    {
        $request = $this->front->Request();
        static::assertNotNull($request);

        return $request;
    }
}

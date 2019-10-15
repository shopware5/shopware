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

use ShopwarePlugin\PaymentMethods\Components\BasePaymentMethod;

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
class sAdminTest extends PHPUnit\Framework\TestCase
{
    /**
     * @var sAdmin
     */
    private $module;

    /**
     * @var sBasket
     */
    private $basketModule;

    /**
     * @var sSystem
     */
    private $systemModule;

    /**
     * @var Shopware_Components_Config
     */
    private $config;

    /**
     * @var Enlight_Components_Session_Namespace The session data
     */
    private $session;

    /**
     * @var Shopware_Components_Snippet_Manager Snippet manager
     */
    private $snippetManager;

    /**
     * @var Enlight_Controller_Front
     */
    private $front;

    public function setUp(): void
    {
        parent::setUp();

        Shopware()->Container()->get('models')->clear();
        Shopware()->Front()->setRequest(new Enlight_Controller_Request_RequestHttp());

        $this->module = Shopware()->Modules()->Admin();
        $this->config = Shopware()->Config();
        $this->session = Shopware()->Session();
        $this->front = Shopware()->Front();
        $this->snippetManager = Shopware()->Snippets();
        $this->basketModule = Shopware()->Modules()->Basket();
        $this->systemModule = Shopware()->System();
        $this->systemModule->sCurrency = Shopware()->Db()->fetchRow('SELECT * FROM s_core_currencies WHERE currency LIKE "EUR"');
        $this->systemModule->sSESSION_ID = null;
        $this->session->offsetSet('sessionId', null);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Shopware()->Container()->get('models')->clear();
    }

    /**
     * @covers \sAdmin::sGetPaymentMeanById
     */
    public function testsGetPaymentMeanById()
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

        static::assertEquals($this->config->get('defaultPayment'), $customer->getPaymentId());

        $this->deleteDummyCustomer($customer);
    }

    /**
     * @covers \sAdmin::sGetPaymentMeans
     */
    public function testsGetPaymentMeans()
    {
        $result = $this->module->sGetPaymentMeans();
        foreach ($result as $paymentMean) {
            static::assertArrayHasKey('id', $paymentMean);
            static::assertArrayHasKey('name', $paymentMean);
            static::assertArrayHasKey('description', $paymentMean);
            static::assertArrayHasKey('debit_percent', $paymentMean);
            static::assertArrayHasKey('surcharge', $paymentMean);
            static::assertArrayHasKey('surchargestring', $paymentMean);
            static::assertArrayHasKey('active', $paymentMean);
            static::assertArrayHasKey('esdactive', $paymentMean);
            static::assertContains($paymentMean['id'], [3, 5, 6]);
        }
    }

    /**
     * @covers \sAdmin::sInitiatePaymentClass
     */
    public function testsInitiatePaymentClass()
    {
        $payments = Shopware()->Models()->getRepository('Shopware\Models\Payment\Payment')->findAll();

        foreach ($payments as $payment) {
            $paymentClass = $this->module->sInitiatePaymentClass($this->module->sGetPaymentMeanById($payment->getId()));
            if (is_bool($paymentClass)) {
                static::assertFalse($paymentClass);
            } else {
                static::assertInstanceOf('ShopwarePlugin\PaymentMethods\Components\BasePaymentMethod', $paymentClass);
                Shopware()->Front()->setRequest(new Enlight_Controller_Request_RequestHttp());

                $requestData = Shopware()->Front()->Request()->getParams();
                $validationResult = $paymentClass->validate($requestData);
                static::assertTrue(is_array($validationResult));
                if (count($validationResult)) {
                    static::assertArrayHasKey('sErrorFlag', $validationResult);
                    static::assertArrayHasKey('sErrorMessages', $validationResult);
                }
            }
        }
    }

    /**
     * @covers \sAdmin::sValidateStep3
     */
    public function testExceptionInsValidateStep3()
    {
        $this->expectException('Enlight_Exception');
        $this->expectExceptionMessage('sValidateStep3 #00: No payment id');
        $this->module->sValidateStep3();
    }

    /**
     * @covers \sAdmin::sValidateStep3
     */
    public function testsValidateStep3()
    {
        $this->front->Request()->setPost('sPayment', 2);

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

    /**
     * @covers \sAdmin::sUpdateNewsletter
     */
    public function testsUpdateNewsletter()
    {
        $email = uniqid(rand()) . 'test@foobar.com';

        // Test insertion
        static::assertTrue($this->module->sUpdateNewsletter(true, $email));
        $newsletterSubscription = Shopware()->Db()->fetchRow(
            'SELECT * FROM s_campaigns_mailaddresses WHERE email = ?',
            [$email]
        );
        static::assertNotNull($newsletterSubscription);
        static::assertEquals(0, $newsletterSubscription['customer']);
        static::assertEquals(1, $newsletterSubscription['groupID']);

        // Test removal
        static::assertTrue($this->module->sUpdateNewsletter(false, $email));
        $newsletterSubscription = Shopware()->Db()->fetchRow(
            'SELECT * FROM s_campaigns_mailaddresses WHERE email = ?',
            [$email]
        );
        static::assertFalse($newsletterSubscription);

        // Retest insertion for customers
        static::assertTrue($this->module->sUpdateNewsletter(true, $email, true));
        $newsletterSubscription = Shopware()->Db()->fetchRow(
            'SELECT * FROM s_campaigns_mailaddresses WHERE email = ?',
            [$email]
        );
        static::assertNotNull($newsletterSubscription);
        static::assertEquals(1, $newsletterSubscription['customer']);
        static::assertEquals(0, $newsletterSubscription['groupID']);

        // Test removal
        static::assertTrue($this->module->sUpdateNewsletter(false, $email));
        $newsletterSubscription = Shopware()->Db()->fetchRow(
            'SELECT * FROM s_campaigns_mailaddresses WHERE email = ?',
            [$email]
        );
        static::assertFalse($newsletterSubscription);
    }

    /**
     * @covers \sAdmin::sUpdatePayment
     */
    public function testsUpdatePayment()
    {
        // Test no user id
        static::assertFalse($this->module->sUpdatePayment());

        $customer = $this->createDummyCustomer();
        $this->session->offsetSet('sUserId', $customer->getId());

        // Test that operation succeeds even without payment id
        static::assertTrue($this->module->sUpdatePayment());
        static::assertEquals(
            0,
            Shopware()->Db()->fetchOne('SELECT paymentID FROM s_user WHERE id = ?', [$customer->getId()])
        );

        // Setup dummy test data and test with it
        $this->front->Request()->setPost([
            'sPayment' => 2,
        ]);
        static::assertTrue($this->module->sUpdatePayment());
        static::assertEquals(
            2,
            Shopware()->Db()->fetchOne('SELECT paymentID FROM s_user WHERE id = ?', [$customer->getId()])
        );

        $this->deleteDummyCustomer($customer);
    }

    /**
     * @covers \sAdmin::sLogin
     */
    public function testsLogin()
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
        $this->front->Request()->setPost([
            'email' => uniqid(rand()) . 'test',
            'password' => uniqid(rand()) . 'test',
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
        $this->front->Request()->setPost([
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
        Shopware()->Db()->update(
            's_user',
            [
                'password' => md5('fooobar'),
                'encoder' => 'md5',
            ],
            'id = ' . $customer->getId()
        );

        $this->front->Request()->setPost([
            'email' => $customer->getEmail(),
            'passwordMD5' => uniqid(rand()),
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
        $this->front->Request()->setPost([
            'email' => $customer->getEmail(),
            'passwordMD5' => md5('fooobar'),
        ]);
        $result = $this->module->sLogin(true);
        static::assertIsArray($result);
        static::assertArrayHasKey('sErrorFlag', $result);
        static::assertArrayHasKey('sErrorMessages', $result);
        static::assertNull($result['sErrorFlag']);
        static::assertNull($result['sErrorMessages']);

        $modifiedMd5User = Shopware()->Db()->fetchRow(
            'SELECT * FROM s_user WHERE id = ?',
            [$customer->getId()]
        );

        // Test that it's the same user, but with different last login
        static::assertEquals($modifiedMd5User['email'], $customer->getEmail());
        static::assertEquals($modifiedMd5User['password'], md5('fooobar'));
        static::assertNotEquals($modifiedMd5User['lastlogin'], $customer->getLastLogin()->format('Y-m-d H:i:s'));

        // Test inactive account
        Shopware()->Db()->update('s_user', ['active' => 0], 'id = ' . $customer->getId());
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
        Shopware()->Db()->update('s_user', ['active' => 1], 'id = ' . $customer->getId());
        $this->front->Request()->setPost([
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
    }

    /**
     * @covers \sAdmin::sCheckUser
     */
    public function testsCheckUser()
    {
        $customer = $this->createDummyCustomer();

        // Basic failing case
        static::assertFalse($this->module->sCheckUser());

        // Test successful login
        $this->front->Request()->setPost([
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
        Shopware()->Db()->update('s_user', ['lastlogin' => '2000-01-01 00:00:00'], 'id = ' . $customer->getId());
        static::assertFalse($this->module->sCheckUser());

        static::assertEquals($customer->getGroup()->getKey(), $this->session->offsetGet('sUserGroup'));
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
    }

    /**
     * @covers \sAdmin::sGetCountryTranslation
     */
    public function testsGetCountryTranslation()
    {
        // Backup existing data and inject demo data
        $existingData = Shopware()->Db()->fetchRow("
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

        if ($existingData) {
            Shopware()->Db()->update('s_core_translations', $demoData, 'id = ' . $existingData['id']);
        } else {
            Shopware()->Db()->insert('s_core_translations', $demoData);
        }

        // Test loading all data, should return the test data
        $shopId = Shopware()->Container()->get('shopware_storefront.context_service')->getShopContext()->getShop()->getId();
        Shopware()->Container()->get('shopware_storefront.context_service')->getShopContext()->getShop()->setId(2);

        $result = $this->module->sGetCountryTranslation();
        static::assertCount(2, $result);
        static::assertArrayHasKey(2, $result);
        static::assertArrayHasKey(5, $result);
        static::assertArrayHasKey('active', $result[2]);
        static::assertArrayHasKey('countryname', $result[2]);
        static::assertEquals(1, $result[2]['active']);
        static::assertEquals('Germany', $result[2]['countryname']);
        static::assertArrayHasKey('active', $result[5]);
        static::assertArrayHasKey('countryname', $result[5]);
        static::assertEquals(1, $result[5]['active']);
        static::assertEquals('Belgium', $result[5]['countryname']);

        // Test with just one country
        $result = $this->module->sGetCountryTranslation(['id' => 2, 'randomField' => 'randomValue']);
        static::assertCount(4, $result);
        static::assertArrayHasKey('id', $result);
        static::assertArrayHasKey('active', $result);
        static::assertArrayHasKey('countryname', $result);
        static::assertArrayHasKey('randomField', $result);
        static::assertEquals(2, $result['id']);
        static::assertEquals(1, $result['active']);
        static::assertEquals('Germany', $result['countryname']);
        static::assertEquals('randomValue', $result['randomField']);

        // If backup data exists, restore it
        if ($existingData) {
            $existingDataId = $existingData['id'];
            unset($existingData['id']);
            Shopware()->Db()->update('s_core_translations', $existingData, 'id = ' . $existingDataId);
        }

        Shopware()->Container()->get('shopware_storefront.context_service')->getShopContext()->getShop()->setId($shopId);
    }

    /**
     * @covers \sAdmin::sGetDispatchTranslation
     */
    public function testsGetDispatchTranslation()
    {
        // Backup existing data and inject demo data
        $existingData = Shopware()->Db()->fetchRow("
            SELECT * FROM s_core_translations
            WHERE objecttype = 'config_dispatch' AND objectlanguage = 2
        ");

        $demoData = [
            'objectkey' => 1,
            'objectlanguage' => 2,
            'objecttype' => 'config_dispatch',
            'objectdata' => serialize(
                [
                    9 => [
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

        if ($existingData) {
            Shopware()->Db()->update('s_core_translations', $demoData, 'id = ' . $existingData['id']);
        } else {
            Shopware()->Db()->insert('s_core_translations', $demoData);
        }

        // Test loading all data, should return the test data
        $shopId = Shopware()->Container()->get('shopware_storefront.context_service')->getShopContext()->getShop()->getId();
        Shopware()->Container()->get('shopware_storefront.context_service')->getShopContext()->getShop()->setId(2);

        $result = $this->module->sGetDispatchTranslation();
        static::assertCount(2, $result);
        static::assertArrayHasKey(9, $result);
        static::assertArrayHasKey(10, $result);
        static::assertArrayHasKey('dispatch_name', $result[9]);
        static::assertArrayHasKey('dispatch_description', $result[9]);
        static::assertArrayHasKey('dispatch_status_link', $result[9]);
        static::assertArrayHasKey('dispatch_name', $result[10]);
        static::assertArrayHasKey('dispatch_description', $result[10]);
        static::assertArrayHasKey('dispatch_status_link', $result[10]);
        static::assertEquals('Standard shipping', $result[9]['dispatch_name']);
        static::assertEquals('Standard shipping description', $result[9]['dispatch_description']);
        static::assertEquals('http://www.dhl.com', $result[9]['dispatch_status_link']);
        static::assertEquals('Shipping by weight', $result[10]['dispatch_name']);
        static::assertEquals('Shipping by weight description', $result[10]['dispatch_description']);
        static::assertEquals('url', $result[10]['dispatch_status_link']);

        // Test with just one shipping method
        $result = $this->module->sGetDispatchTranslation(['id' => 9, 'randomField' => 'randomValue']);
        static::assertCount(5, $result);
        static::assertArrayHasKey('id', $result);
        static::assertArrayHasKey('name', $result);
        static::assertArrayHasKey('description', $result);
        static::assertArrayHasKey('status_link', $result);
        static::assertArrayHasKey('randomField', $result);
        static::assertEquals(9, $result['id']);
        static::assertEquals('Standard shipping', $result['name']);
        static::assertEquals('Standard shipping description', $result['description']);
        static::assertEquals('http://www.dhl.com', $result['status_link']);
        static::assertEquals('randomValue', $result['randomField']);

        // If backup data exists, restore it
        if ($existingData) {
            $existingDataId = $existingData['id'];
            unset($existingData['id']);
            Shopware()->Db()->update('s_core_translations', $existingData, 'id = ' . $existingDataId);
        }

        Shopware()->Container()->get('shopware_storefront.context_service')->getShopContext()->getShop()->setId($shopId);
    }

    /**
     * @covers \sAdmin::sGetPaymentTranslation
     */
    public function testsGetPaymentTranslation()
    {
        // Backup existing data and inject demo data
        $existingData = Shopware()->Db()->fetchRow("
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

        if ($existingData) {
            Shopware()->Db()->update('s_core_translations', $demoData, 'id = ' . $existingData['id']);
        } else {
            Shopware()->Db()->insert('s_core_translations', $demoData);
        }

        // Test loading all data, should return the test data
        $shopId = Shopware()->Container()->get('shopware_storefront.context_service')->getShopContext()->getShop()->getId();
        Shopware()->Container()->get('shopware_storefront.context_service')->getShopContext()->getShop()->setId(2);

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
        static::assertEquals('Debit', $result[2]['description']);
        static::assertEquals('Additional text', $result[2]['additionalDescription']);
        static::assertEquals('Cash on delivery', $result[3]['description']);
        static::assertEquals('(including 2.00 Euro VAT)', $result[3]['additionalDescription']);
        static::assertEquals('Paid in advance', $result[5]['description']);
        static::assertEquals('The goods are delivered directly upon receipt of payment.', $result[5]['additionalDescription']);

        // Test with just one payment mean
        $result = $this->module->sGetPaymentTranslation(['id' => 2, 'randomField' => 'randomValue']);
        static::assertCount(4, $result);
        static::assertArrayHasKey('id', $result);
        static::assertArrayHasKey('description', $result);
        static::assertArrayHasKey('additionaldescription', $result);
        static::assertArrayHasKey('randomField', $result);
        static::assertEquals(2, $result['id']);
        static::assertEquals('Debit', $result['description']);
        static::assertEquals('Additional text', $result['additionaldescription']);
        static::assertEquals('randomValue', $result['randomField']);

        // If backup data exists, restore it
        if ($existingData) {
            $existingDataId = $existingData['id'];
            unset($existingData['id']);
            Shopware()->Db()->update('s_core_translations', $existingData, 'id = ' . $existingDataId);
        }

        Shopware()->Container()->get('shopware_storefront.context_service')->getShopContext()->getShop()->setId($shopId);
    }

    /**
     * @covers \sAdmin::sGetCountryStateTranslation
     */
    public function testsGetCountryStateTranslation()
    {
        // Backup existing data and inject demo data
        $existingData = Shopware()->Db()->fetchRow("
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

        if ($existingData) {
            Shopware()->Db()->update('s_core_translations', $demoData, 'id = ' . $existingData['id']);
        } else {
            Shopware()->Db()->insert('s_core_translations', $demoData);
        }

        // Test with default shop, return empty array
        static::assertCount(0, $this->module->sGetCountryStateTranslation());

        // Hack the current system shop, so we can properly test this
        Shopware()->Shop()->setDefault(false);

        $result = $this->module->sGetCountryStateTranslation();
        static::assertCount(2, $result);
        static::assertArrayHasKey(23, $result);
        static::assertArrayHasKey(24, $result);
        static::assertArrayHasKey('name', $result[23]);
        static::assertArrayHasKey('name', $result[24]);
        static::assertEquals('Arkansas (english)', $result[23]['name']);
        static::assertEquals('California', $result[24]['name']);

        // Create a stub of a Shop for fallback.
        $shopFallbackId = Shopware()->Container()->get('shopware_storefront.context_service')->getShopContext()->getShop()->getFallbackId();
        Shopware()->Container()->get('shopware_storefront.context_service')->getShopContext()->getShop()->setFallbackId(10000);

        Shopware()->Db()->insert('s_core_translations', [
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
        static::assertEquals('asdfasfdasdfa', $result[2]['name']);
        static::assertEquals('Arkansas (english)', $result[23]['name']);
        static::assertEquals('California', $result[24]['name']);

        // If backup data exists, restore it
        if ($existingData) {
            $existingDataId = $existingData['id'];
            unset($existingData['id']);
            Shopware()->Db()->update('s_core_translations', $existingData, 'id = ' . $existingDataId);
        }
        Shopware()->Db()->delete('s_core_translations', 'objectlanguage = 10000');

        Shopware()->Container()->get('shopware_storefront.context_service')->getShopContext()->getShop()->setFallbackId($shopFallbackId);
    }

    /**
     * @covers \sAdmin::sGetCountryList
     */
    public function testsGetCountryList()
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
        $existingCountryData = Shopware()->Db()->fetchRow("
            SELECT * FROM s_core_translations
            WHERE objecttype = 'config_countries' AND objectlanguage = 1
        ");
        $existingStateData = Shopware()->Db()->fetchRow("
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

        if ($existingCountryData) {
            Shopware()->Db()->update('s_core_translations', $demoCountryData, 'id = ' . $existingCountryData['id']);
        } else {
            Shopware()->Db()->insert('s_core_translations', $demoCountryData);
        }
        if ($existingStateData) {
            Shopware()->Db()->update('s_core_translations', $demoStateData, 'id = ' . $existingStateData['id']);
        } else {
            Shopware()->Db()->insert('s_core_translations', $demoStateData);
        }

        //Hack current context, so next test works
        $context = Shopware()->Container()->get('shopware_storefront.context_service')->getShopContext();
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
        static::assertEquals('Germany', $country['countryname']);

        // Make Germany display states, so we can test it
        $existingGermanyData = Shopware()->Db()->fetchRow("
            SELECT * FROM s_core_countries
            WHERE countryiso = 'DE'
        ");
        Shopware()->Db()->update(
            's_core_countries',
            ['display_state_in_registration' => 1],
            'id = ' . $existingGermanyData['id']
        );

        // Test with translations and states
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
        static::assertCount(16, $country['states']);
        static::assertEquals('Germany', $country['countryname']);
        foreach ($country['states'] as $state) {
            static::assertArrayHasKey('id', $state);
            static::assertArrayHasKey('countryID', $state);
            static::assertArrayHasKey('name', $state);
            static::assertArrayHasKey('shortcode', $state);
            static::assertArrayHasKey('active', $state);
        }
        static::assertContains('111', array_column($country['states'], 'name'));

        // If backup data exists, restore it
        if ($existingCountryData) {
            $existingCountryDataId = $existingCountryData['id'];
            unset($existingCountryData['id']);
            Shopware()->Db()->update('s_core_translations', $existingCountryData, 'id = ' . $existingCountryDataId);
        }
        if ($existingStateData) {
            $existingStateDataId = $existingStateData['id'];
            unset($existingStateData['id']);
            Shopware()->Db()->update('s_core_translations', $existingStateData, 'id = ' . $existingStateDataId);
        }
        if ($existingGermanyData) {
            $existingGermanyDataId = $existingGermanyData['id'];
            unset($existingGermanyData['id']);
            Shopware()->Db()->update('s_core_countries', $existingGermanyData, 'id = ' . $existingGermanyDataId);
        }

        // Remove shop hack
        $context->getShop()->setIsDefault(true);
    }

    /**
     * @covers \sAdmin::sGetDownloads
     */
    public function testsGetDownloads()
    {
        $customer = $this->createDummyCustomer();
        $this->session->offsetSet('sUserId', $customer->getId());

        // New customers don't have available downloads
        $downloads = $this->module->sGetDownloads();
        static::assertCount(0, $downloads['orderData']);

        // Inject demo data
        $orderData = [
            'ordernumber' => uniqid(rand()),
            'userID' => $customer->getId(),
            'invoice_amount' => '37.99',
            'invoice_amount_net' => '31.92',
            'invoice_shipping' => '0',
            'invoice_shipping_net' => '0',
            'ordertime' => '2014-03-14 10:26:20',
            'status' => '0',
            'cleared' => '17',
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

        Shopware()->Db()->insert('s_order', $orderData);
        $orderId = Shopware()->Db()->lastInsertId();

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
            'releasedate' => '0000-00-00',
            'modus' => '0',
            'esdarticle' => '1',
            'taxID' => '1',
            'tax_rate' => '19',
            'config' => '',
        ];

        Shopware()->Db()->insert('s_order_details', $orderDetailsData);
        $orderDetailId = Shopware()->Db()->lastInsertId();

        $orderEsdData = [
            'serialID' => '8',
            'esdID' => '2',
            'userID' => $customer->getId(),
            'orderID' => $orderId,
            'orderdetailsID' => $orderDetailId,
            'datum' => '2014-03-14 10:26:20',
        ];

        Shopware()->Db()->insert('s_order_esd', $orderEsdData);

        // Mock a login
        $orderEsdId = Shopware()->Db()->lastInsertId();

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
        static::assertEquals($orderData['ordernumber'], $esd['ordernumber']);
        static::assertEquals('37,99', $esd['invoice_amount']);
        static::assertEquals($orderData['invoice_amount_net'], $esd['invoice_amount_net']);
        static::assertEquals($orderData['invoice_shipping'], $esd['invoice_shipping']);
        static::assertEquals($orderData['invoice_shipping_net'], $esd['invoice_shipping_net']);
        static::assertEquals('14.03.2014 10:26', $esd['datum']);
        static::assertEquals($orderData['status'], $esd['status']);
        static::assertEquals($orderData['cleared'], $esd['cleared']);
        static::assertEquals($orderData['comment'], $esd['comment']);
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
     * @covers \sAdmin::sGetOpenOrderData
     * @depends testsGetDownloads
     * @ticket SW-5653
     */
    public function testsGetOpenOrderData($demoData)
    {
        // Inherit data from previous test
        $customer = $demoData['customer'];
        $oldOrderId = $demoData['orderId'];
        $orderEsdId = $demoData['orderEsdId'];
        $orderNumber = uniqid(rand());

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

        Shopware()->Db()->insert('s_order', $orderData);
        $orderId = Shopware()->Db()->lastInsertId();

        Shopware()->Db()->query("
            INSERT IGNORE INTO `s_order_details` (`orderID`, `ordernumber`, `articleID`, `articleordernumber`, `price`, `quantity`, `name`, `status`, `shipped`, `shippedgroup`, `releasedate`, `modus`, `esdarticle`, `taxID`, `tax_rate`, `config`) VALUES
            (?, ?, 12, 'SW10012', 9.99, 1, 'Kobra Vodka 37,5%', 0, 0, 0, '0000-00-00', 0, 0, 1, 19, ''),
            (?, ?, 0, 'SHIPPINGDISCOUNT', -2, 1, 'Warenkorbrabatt', 0, 0, 0, '0000-00-00', 4, 0, 0, 19, ''),
            (?, ?, 0, 'sw-surcharge', 5, 1, 'Mindermengenzuschlag', 0, 0, 0, '0000-00-00', 4, 0, 0, 19, '');
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

        static::assertCount(2, $result);
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
                static::assertEquals($orderNumber, $order['ordernumber']);
                static::assertEquals($customer->getId(), $order['userID']);
                break;
            }
        }

        Shopware()->Db()->delete('s_order_esd', 'id = ' . $orderEsdId);
        Shopware()->Db()->delete('s_order_details', 'orderID = ' . $orderId);
        Shopware()->Db()->delete('s_order_details', 'orderID = ' . $oldOrderId);
        Shopware()->Db()->delete('s_order', 'id = ' . $orderId);
        Shopware()->Db()->delete('s_order', 'id = ' . $oldOrderId);
        $this->deleteDummyCustomer($customer);
    }

    /**
     * @covers \sAdmin::sGetUserMailById
     * @covers \sAdmin::sGetUserByMail
     * @covers \sAdmin::sGetUserNameById
     */
    public function testGetEmailAndUser()
    {
        $customer = $this->createDummyCustomer();

        // Test sGetUserMailById with null and expected cases
        static::assertNull($this->module->sGetUserMailById());
        $this->session->offsetSet('sUserId', $customer->getId());
        static::assertEquals($customer->getEmail(), $this->module->sGetUserMailById());

        // Test sGetUserByMail with null and expected cases
        static::assertNull($this->module->sGetUserByMail(uniqid(rand())));
        static::assertEquals($customer->getId(), $this->module->sGetUserByMail($customer->getEmail()));

        // Test sGetUserNameById with null and expected cases
        static::assertEmpty($this->module->sGetUserNameById(uniqid(rand())));
        static::assertEquals(
            ['firstname' => 'Max', 'lastname' => 'Mustermann'],
            $this->module->sGetUserNameById($customer->getId())
        );

        $this->deleteDummyCustomer($customer);
    }

    /**
     * @covers \sAdmin::sGetUserData
     */
    public function testsGetUserDataWithoutLogin()
    {
        static::assertEquals(
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

    /**
     * @covers \sAdmin::sGetUserData
     */
    public function testsGetUserDataWithLogin()
    {
        $customer = $this->createDummyCustomer();
        $this->session->offsetSet('sUserId', $customer->getId());
        $this->session->offsetUnset('sState');

        $result = $this->module->sGetUserData();

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
                    'countryname' => 'Germany',
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
                    'firstlogin' => $customer->getFirstLogin()->format('Y-m-d'),
                    'lastlogin' => $customer->getLastLogin()->format('Y-m-d H:i:s'),
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
    }

    /**
     * @covers \sAdmin::sManageRisks
     * @covers \sAdmin::sRiskORDERVALUELESS
     * @covers \sAdmin::sRiskORDERVALUEMORE
     * @covers \sAdmin::sRiskCUSTOMERGROUPIS
     * @covers \sAdmin::sRiskCUSTOMERGROUPISNOT
     * @covers \sAdmin::sRiskZIPCODE
     * @covers \sAdmin::sRiskBILLINGZIPCODE
     * @covers \sAdmin::sRiskZONEIS
     * @covers \sAdmin::sRiskBILLINGZONEIS
     * @covers \sAdmin::sRiskZONEISNOT
     * @covers \sAdmin::sRiskBILLINGZONEISNOT
     * @covers \sAdmin::sRiskLANDIS
     * @covers \sAdmin::sRiskBILLINGLANDIS
     * @covers \sAdmin::sRiskLANDISNOT
     * @covers \sAdmin::sRiskBILLINGLANDISNOT
     * @covers \sAdmin::sRiskNEWCUSTOMER
     * @covers \sAdmin::sRiskORDERPOSITIONSMORE
     * @covers \sAdmin::sRiskATTRIS
     * @covers \sAdmin::sRiskATTRISNOT
     * @covers \sAdmin::sRiskDUNNINGLEVELONE
     * @covers \sAdmin::sRiskDUNNINGLEVELTWO
     * @covers \sAdmin::sRiskDUNNINGLEVELTHREE
     * @covers \sAdmin::sRiskINKASSO
     * @covers \sAdmin::sRiskLASTORDERLESS
     * @covers \sAdmin::sRiskARTICLESFROM
     * @covers \sAdmin::sRiskLASTORDERSLESS
     * @covers \sAdmin::sRiskPREGSTREET
     * @covers \sAdmin::sRiskDIFFER
     * @covers \sAdmin::sRiskCUSTOMERNR
     * @covers \sAdmin::sRiskLASTNAME
     * @covers \sAdmin::sRiskSUBSHOP
     * @covers \sAdmin::sRiskSUBSHOPNOT
     * @covers \sAdmin::sRiskCURRENCIESISOIS
     * @covers \sAdmin::sRiskCURRENCIESISOISNOT
     */
    public function testsManageRisks()
    {
        $customer = $this->createDummyCustomer();
        $this->session->offsetSet('sUserId', $customer->getId());

        $basket = [
            'content' => 1,
            'AmountNumeric' => 10,
        ];
        $user = $this->module->sGetUserData();

        $date = new DateTime();

        // Inject demo data
        $orderData = [
            'ordernumber' => uniqid(rand()),
            'userID' => $customer->getId(),
            'invoice_amount' => '37.99',
            'invoice_amount_net' => '31.92',
            'invoice_shipping' => '0',
            'invoice_shipping_net' => '0',
            'ordertime' => $date->format('Y-m-d H:i:s'),
            'status' => '0',
            'cleared' => '17',
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

        Shopware()->Db()->insert('s_order', $orderData);
        $orderId = Shopware()->Db()->lastInsertId();

        // No rules, returns false
        static::assertFalse($this->module->sManageRisks(2, $basket, $user));

        // Test all rules

        // sRiskORDERVALUELESS
        Shopware()->Db()->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'ORDERVALUELESS',
                'value1' => 20,
            ]
        );
        $firstTestRuleId = Shopware()->Db()->lastInsertId();
        static::assertTrue($this->module->sManageRisks(2, $basket, $user));

        // sRiskORDERVALUEMORE
        Shopware()->Db()->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'ORDERVALUEMORE',
                'value1' => 20,
            ]
        );
        // Test 'OR' logic between different rules (only one needs to be true)
        static::assertTrue($this->module->sManageRisks(2, $basket, $user));

        // Deleting the first rule, only a false one is left
        Shopware()->Db()->delete('s_core_rulesets', 'id = ' . $firstTestRuleId);
        static::assertFalse($this->module->sManageRisks(2, $basket, $user));
        Shopware()->Db()->delete('s_core_rulesets', 'id >= ' . $firstTestRuleId);

        // sRiskCUSTOMERGROUPIS
        // sRiskCUSTOMERGROUPISNOT
        Shopware()->Db()->insert(
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
        static::assertFalse($this->module->sManageRisks(2, $basket, $user));
        Shopware()->Db()->delete('s_core_rulesets', 'id >= ' . $firstTestRuleId);

        // sRiskZIPCODE
        Shopware()->Db()->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'ZIPCODE',
                'value1' => '98765',
            ]
        );
        static::assertTrue($this->module->sManageRisks(2, $basket, $user));
        Shopware()->Db()->delete('s_core_rulesets', 'id >= ' . $firstTestRuleId);

        // sRiskBILLINGZIPCODE
        Shopware()->Db()->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'BILLINGZIPCODE',
                'value1' => '12345',
            ]
        );

        static::assertTrue($this->module->sManageRisks(2, $basket, $user));
        Shopware()->Db()->delete('s_core_rulesets', 'id >= ' . $firstTestRuleId);

        // sRiskZONEIS
        Shopware()->Db()->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'ZONEIS',
                'value1' => '12345',
            ]
        );
        static::assertFalse($this->module->sManageRisks(2, $basket, $user));
        Shopware()->Db()->delete('s_core_rulesets', 'id >= ' . $firstTestRuleId);

        // sRiskZONEISNOT
        Shopware()->Db()->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'ZONEISNOT',
                'value1' => '12345',
            ]
        );
        static::assertTrue($this->module->sManageRisks(2, $basket, $user));
        Shopware()->Db()->delete('s_core_rulesets', 'id >= ' . $firstTestRuleId);

        // sRiskLANDIS
        // sRiskLANDISNOT
        Shopware()->Db()->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'LANDIS',
                'value1' => 'AU',
                'rule2' => 'LANDISNOT',
                'value2' => 'UK',
            ]
        );
        static::assertTrue($this->module->sManageRisks(2, $basket, $user));
        Shopware()->Db()->delete('s_core_rulesets', 'id >= ' . $firstTestRuleId);

        // sRiskBILLINGLANDIS
        // sRiskBILLINGLANDISNOT
        Shopware()->Db()->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'BILLINGLANDIS',
                'value1' => 'DE',
                'rule2' => 'BILLINGLANDISNOT',
                'value2' => 'UK',
            ]
        );
        static::assertTrue($this->module->sManageRisks(2, $basket, $user));
        Shopware()->Db()->delete('s_core_rulesets', 'id >= ' . $firstTestRuleId);

        // sRiskNEWCUSTOMER
        Shopware()->Db()->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'NEWCUSTOMER',
            ]
        );
        static::assertTrue($this->module->sManageRisks(2, $basket, $user));
        Shopware()->Db()->delete('s_core_rulesets', 'id >= ' . $firstTestRuleId);

        // sRiskORDERPOSITIONSMORE
        Shopware()->Db()->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'ORDERPOSITIONSMORE',
                'value1' => '2',
            ]
        );
        static::assertFalse($this->module->sManageRisks(2, $basket, $user));
        Shopware()->Db()->delete('s_core_rulesets', 'id >= ' . $firstTestRuleId);

        $this->module->sSYSTEM->sSESSION_ID = uniqid(rand());
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);
        $this->basketModule->sAddArticle('SW10118.8');

        // sRiskATTRIS
        Shopware()->Db()->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'ATTRIS',
                'value1' => '1|0',
            ]
        );

        $fullBasket = $this->basketModule->sGetBasket();
        static::assertTrue($this->module->sManageRisks(2, $fullBasket, $user));
        Shopware()->Db()->delete('s_core_rulesets', 'id >= ' . $firstTestRuleId);

        $this->basketModule->sAddArticle('SW10118.8');
        // sRiskATTRISNOT
        Shopware()->Db()->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'ATTRISNOT',
                'value1' => '17|null',
            ]
        );
        static::assertFalse($this->module->sManageRisks(2, $fullBasket, $user));
        Shopware()->Db()->delete('s_core_rulesets', 'id >= ' . $firstTestRuleId);

        // sRiskDUNNINGLEVELONE
        Shopware()->Db()->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'DUNNINGLEVELONE',
            ]
        );
        static::assertFalse($this->module->sManageRisks(2, $fullBasket, $user));
        Shopware()->Db()->delete('s_core_rulesets', 'id >= ' . $firstTestRuleId);

        // sRiskDUNNINGLEVELTWO
        Shopware()->Db()->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'DUNNINGLEVELTWO',
            ]
        );
        static::assertFalse($this->module->sManageRisks(2, $fullBasket, $user));
        Shopware()->Db()->delete('s_core_rulesets', 'id >= ' . $firstTestRuleId);

        // sRiskDUNNINGLEVELTHREE
        Shopware()->Db()->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'DUNNINGLEVELTHREE',
            ]
        );
        static::assertFalse($this->module->sManageRisks(2, $fullBasket, $user));
        Shopware()->Db()->delete('s_core_rulesets', 'id >= ' . $firstTestRuleId);

        // sRiskINKASSO
        Shopware()->Db()->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'INKASSO',
            ]
        );
        static::assertTrue($this->module->sManageRisks(2, $fullBasket, $user));
        Shopware()->Db()->delete('s_core_rulesets', 'id >= ' . $firstTestRuleId);

        // sRiskLASTORDERLESS
        Shopware()->Db()->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'LASTORDERLESS',
                'value1' => '1',
            ]
        );
        static::assertTrue($this->module->sManageRisks(2, $fullBasket, $user));
        Shopware()->Db()->delete('s_core_rulesets', 'id >= ' . $firstTestRuleId);

        // sRiskARTICLESFROM
        Shopware()->Db()->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'ARTICLESFROM',
                'value1' => '1',
            ]
        );
        static::assertFalse($this->module->sManageRisks(2, $fullBasket, $user));
        Shopware()->Db()->delete('s_core_rulesets', 'id >= ' . $firstTestRuleId);

        // sRiskARTICLESFROM
        Shopware()->Db()->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'ARTICLESFROM',
                'value1' => '9',
            ]
        );
        static::assertTrue($this->module->sManageRisks(2, $fullBasket, $user));
        Shopware()->Db()->delete('s_core_rulesets', 'id >= ' . $firstTestRuleId);

        // sRiskLASTORDERSLESS
        Shopware()->Db()->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'LASTORDERSLESS',
                'value1' => '9',
            ]
        );
        static::assertTrue($this->module->sManageRisks(2, $fullBasket, $user));
        Shopware()->Db()->delete('s_core_rulesets', 'id >= ' . $firstTestRuleId);

        // sRiskLASTORDERSLESS
        Shopware()->Db()->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'LASTORDERSLESS',
                'value1' => '0',
            ]
        );
        static::assertFalse($this->module->sManageRisks(2, $fullBasket, $user));
        Shopware()->Db()->delete('s_core_rulesets', 'id >= ' . $firstTestRuleId);

        // sRiskPREGSTREET
        Shopware()->Db()->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'PREGSTREET',
                'value1' => 'Merkel',
            ]
        );
        static::assertTrue($this->module->sManageRisks(2, $fullBasket, $user));
        Shopware()->Db()->delete('s_core_rulesets', 'id >= ' . $firstTestRuleId);

        // sRiskPREGSTREET
        Shopware()->Db()->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'PREGSTREET',
                'value1' => 'Google',
            ]
        );
        static::assertFalse($this->module->sManageRisks(2, $fullBasket, $user));
        Shopware()->Db()->delete('s_core_rulesets', 'id >= ' . $firstTestRuleId);

        // sRiskPREGBILLINGSTREET
        Shopware()->Db()->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'PREGBILLINGSTREET',
                'value1' => 'Google',
            ]
        );
        static::assertFalse($this->module->sManageRisks(2, $fullBasket, $user));
        Shopware()->Db()->delete('s_core_rulesets', 'id >= ' . $firstTestRuleId);

        // sRiskDIFFER
        Shopware()->Db()->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'DIFFER',
            ]
        );
        static::assertTrue($this->module->sManageRisks(2, $fullBasket, $user));
        Shopware()->Db()->delete('s_core_rulesets', 'id >= ' . $firstTestRuleId);

        // sRiskCUSTOMERNR
        Shopware()->Db()->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'CUSTOMERNR',
                'value1' => $customer->getNumber(),
            ]
        );
        static::assertTrue($this->module->sManageRisks(2, $fullBasket, $user));
        Shopware()->Db()->delete('s_core_rulesets', 'id >= ' . $firstTestRuleId);

        // sRiskCUSTOMERNR
        Shopware()->Db()->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'CUSTOMERNR',
                'value1' => 'ThisIsNeverGoingToBeACustomerNumber',
            ]
        );
        static::assertFalse($this->module->sManageRisks(2, $fullBasket, $user));
        Shopware()->Db()->delete('s_core_rulesets', 'id >= ' . $firstTestRuleId);

        // sRiskLASTNAME
        Shopware()->Db()->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'LASTNAME',
                'value1' => 'Mustermann',
            ]
        );
        static::assertTrue($this->module->sManageRisks(2, $fullBasket, $user));
        Shopware()->Db()->delete('s_core_rulesets', 'id >= ' . $firstTestRuleId);

        // sRiskLASTNAME
        Shopware()->Db()->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'LASTNAME',
                'value1' => 'NotMustermann',
            ]
        );
        static::assertFalse($this->module->sManageRisks(2, $fullBasket, $user));
        Shopware()->Db()->delete('s_core_rulesets', 'id >= ' . $firstTestRuleId);

        // sRiskSUBSHOP
        Shopware()->Db()->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'SUBSHOP',
                'value1' => '1',
            ]
        );
        static::assertTrue($this->module->sManageRisks(2, $fullBasket, $user));
        Shopware()->Db()->delete('s_core_rulesets', 'id >= ' . $firstTestRuleId);

        // sRiskSUBSHOP
        Shopware()->Db()->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'SUBSHOP',
                'value1' => '2',
            ]
        );
        static::assertFalse($this->module->sManageRisks(2, $fullBasket, $user));
        Shopware()->Db()->delete('s_core_rulesets', 'id >= ' . $firstTestRuleId);

        // sRiskSUBSHOPNOT
        Shopware()->Db()->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'SUBSHOPNOT',
                'value1' => '2',
            ]
        );
        static::assertTrue($this->module->sManageRisks(2, $fullBasket, $user));
        Shopware()->Db()->delete('s_core_rulesets', 'id >= ' . $firstTestRuleId);

        // sRiskSUBSHOPNOT
        Shopware()->Db()->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'SUBSHOPNOT',
                'value1' => '1',
            ]
        );
        static::assertFalse($this->module->sManageRisks(2, $fullBasket, $user));
        Shopware()->Db()->delete('s_core_rulesets', 'id >= ' . $firstTestRuleId);

        // sRiskCURRENCIESISOIS
        Shopware()->Db()->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'CURRENCIESISOIS',
                'value1' => 'eur',
            ]
        );
        static::assertTrue($this->module->sManageRisks(2, $fullBasket, $user));
        Shopware()->Db()->delete('s_core_rulesets', 'id >= ' . $firstTestRuleId);

        // sRiskCURRENCIESISOIS
        Shopware()->Db()->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'CURRENCIESISOIS',
                'value1' => 'yen',
            ]
        );
        static::assertFalse($this->module->sManageRisks(2, $fullBasket, $user));
        Shopware()->Db()->delete('s_core_rulesets', 'id >= ' . $firstTestRuleId);

        // sRiskCURRENCIESISOISNOT
        Shopware()->Db()->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'CURRENCIESISOISNOT',
                'value1' => 'eur',
            ]
        );
        static::assertFalse($this->module->sManageRisks(2, $fullBasket, $user));
        Shopware()->Db()->delete('s_core_rulesets', 'id >= ' . $firstTestRuleId);

        // sRiskCURRENCIESISOISNOT
        Shopware()->Db()->insert(
            's_core_rulesets',
            [
                'paymentID' => 2,
                'rule1' => 'CURRENCIESISOISNOT',
                'value1' => 'yen',
            ]
        );
        static::assertTrue($this->module->sManageRisks(2, $fullBasket, $user));
        Shopware()->Db()->delete('s_core_rulesets', 'id >= ' . $firstTestRuleId);

        Shopware()->Db()->delete('s_order', 'id = ' . $orderId);
        $this->deleteDummyCustomer($customer);
    }

    /**
     * @covers \sAdmin::sNewsletterSubscription
     */
    public function testsNewsletterSubscriptionWithPostData()
    {
        // Test subscribe with empty post field and empty address, fail validation
        $this->front->Request()->setPost('newsletter', '');
        $result = $this->module->sNewsletterSubscription('');
        static::assertEquals(
            [
                'code' => 5,
                'message' => $this->snippetManager->getNamespace('frontend/account/internalMessages')
                        ->get('ErrorFillIn', 'Please fill in all red fields'),
                'sErrorFlag' => ['newsletter' => true],
            ],
            $result
        );
    }

    /**
     * @covers \sAdmin::sNewsletterSubscription
     */
    public function testsNewsletterSubscription()
    {
        $validAddress = uniqid(rand()) . '@shopware.com';

        // Test unsubscribe with non existing email, fail
        $result = $this->module->sNewsletterSubscription(uniqid(rand()) . '@shopware.com', true);
        static::assertEquals(
            [
                'code' => 4,
                'message' => $this->snippetManager->getNamespace('frontend/account/internalMessages')
                        ->get('NewsletterFailureNotFound', 'This mail address could not be found'),
            ],
            $result
        );

        // Test unsubscribe with empty post field, fail validation
        $result = $this->module->sNewsletterSubscription('', true);
        static::assertEquals(
            [
                'code' => 6,
                'message' => $this->snippetManager->getNamespace('frontend/account/internalMessages')
                        ->get('NewsletterFailureMail', 'Enter eMail address'),
            ],
            $result
        );

        // Test with empty field, fail validation
        $result = $this->module->sNewsletterSubscription('');
        static::assertEquals(
            [
                'code' => 6,
                'message' => $this->snippetManager->getNamespace('frontend/account/internalMessages')
                        ->get('NewsletterFailureMail', 'Enter eMail address'),
            ],
            $result
        );

        // Test with malformed email, fail validation
        $result = $this->module->sNewsletterSubscription('thisIsNotAValidEmailAddress');
        static::assertEquals(
            [
                'code' => 1,
                'message' => $this->snippetManager->getNamespace('frontend/account/internalMessages')
                        ->get('NewsletterFailureInvalid', 'Enter valid eMail address'),
            ],
            $result
        );

        // Check that test email does not exist
        static::assertFalse(
            Shopware()->Db()->fetchRow(
                'SELECT email, groupID FROM s_campaigns_mailaddresses WHERE email LIKE ?',
                [$validAddress]
            )
        );

        // Test with correct unique email, all ok
        $result = $this->module->sNewsletterSubscription($validAddress);
        static::assertEquals(
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
            Shopware()->Db()->fetchRow(
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
            Shopware()->Db()->fetchAll(
                'SELECT email, groupID FROM s_campaigns_maildata WHERE email LIKE ?',
                [$validAddress]
            )
        );

        // Test with same email, fail
        $result = $this->module->sNewsletterSubscription($validAddress);
        static::assertEquals(
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
        static::assertEquals(
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
            Shopware()->Db()->fetchAll(
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
            Shopware()->Db()->fetchAll(
                'SELECT email, groupID FROM s_campaigns_maildata WHERE email LIKE ?',
                [$validAddress]
            )
        );

        // Test unsubscribe the same email, all ok
        $result = $this->module->sNewsletterSubscription($validAddress, true);
        static::assertEquals(
            [
                'code' => 5,
                'message' => $this->snippetManager->getNamespace('frontend/account/internalMessages')
                        ->get('NewsletterMailDeleted', 'Your mail address was deleted'),
            ],
            $result
        );

        // Check that test email address was removed
        static::assertFalse(
            Shopware()->Db()->fetchRow(
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
            Shopware()->Db()->fetchAll(
                'SELECT email, groupID FROM s_campaigns_maildata WHERE email LIKE ?',
                [$validAddress]
            )
        );

        Shopware()->Db()->delete(
            's_campaigns_maildata',
            'email LIKE "' . $validAddress . '"'
        );
    }

    /**
     * @covers \sAdmin::sGetCountry
     */
    public function testsGetCountry()
    {
        // Empty argument, return false
        static::assertFalse($this->module->sGetCountry(''));

        // No matching country, return empty array
        static::assertEquals([], $this->module->sGetCountry(-1));

        // Valid country returns valid data
        $result = $this->module->sGetCountry('de');
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
        static::assertEquals(
            $this->module->sGetCountry($result['id']),
            $result
        );
    }

    /**
     * @covers \sAdmin::sGetPaymentMean
     */
    public function testsGetPaymentmean()
    {
        // Empty argument, return false
        static::assertFalse($this->module->sGetPaymentMean(''));

        // No matching payment mean, return empty array
        static::assertEquals(['country_surcharge' => []], $this->module->sGetPaymentMean(-1));

        // Valid country returns valid data
        $result = $this->module->sGetPaymentMean(
            Shopware()->Db()->fetchOne('SELECT id FROM s_core_paymentmeans WHERE name = "prepayment"')
        );

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
        static::assertEquals(
            $this->module->sGetPaymentMean($result['name']),
            $result
        );
    }

    /**
     * @covers \sAdmin::sGetDispatchBasket
     */
    public function testsGetDispatchBasket()
    {
        // No basket, return false
        static::assertFalse($this->module->sGetDispatchBasket());

        $this->module->sSYSTEM->sSESSION_ID = uniqid(rand());
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);
        $this->basketModule->sAddArticle('SW10118.8');

        // With the correct data, return properly formatted array
        // This is a big query function
        $result = $this->module->sGetDispatchBasket();
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

    /**
     * @covers \sAdmin::sGetPremiumDispatches
     */
    public function testsGetPremiumDispatches()
    {
        // No basket, return empty array,
        static::assertEquals([], $this->module->sGetPremiumDispatches());

        $this->module->sSYSTEM->sSESSION_ID = uniqid(rand());
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);
        $this->basketModule->sAddArticle('SW10118.8');

        $result = $this->module->sGetPremiumDispatches();

        static::assertGreaterThan(0, count($result));
        foreach ($result as $dispatch) {
            static::assertArrayHasKey('id', $dispatch);
            static::assertArrayHasKey('name', $dispatch);
            static::assertArrayHasKey('description', $dispatch);
            static::assertArrayHasKey('calculation', $dispatch);
            static::assertArrayHasKey('status_link', $dispatch);
        }
    }

    /**
     * @covers \sAdmin::sGetPremiumDispatchSurcharge
     */
    public function testsGetPremiumDispatchSurcharge()
    {
        // No basket, return false,
        static::assertFalse($this->module->sGetPremiumDispatchSurcharge(null));

        $this->module->sSYSTEM->sSESSION_ID = uniqid(rand());
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);
        $this->basketModule->sAddArticle('SW10010');
        $fullBasket = $this->module->sGetDispatchBasket();

        $result = $this->module->sGetPremiumDispatchSurcharge($fullBasket);
        static::assertEquals(0, $result);
    }

    /**
     * @covers \sAdmin::sGetPremiumShippingcosts
     */
    public function testsGetPremiumShippingcosts()
    {
        // No basket, return false,
        static::assertFalse($this->module->sGetPremiumShippingcosts());

        $countries = $this->module->sGetCountryList();
        foreach ($countries as $country) {
            if ($country['countryiso']) {
                $germany = $country;
                break;
            }
        }

        $this->module->sSYSTEM->sSESSION_ID = uniqid(rand());
        $this->session->offsetSet('sessionId', $this->module->sSYSTEM->sSESSION_ID);
        $this->basketModule->sAddArticle('SW10010');

        // With country data, no dispatch method
        static::assertEquals(
            ['brutto' => 0, 'netto' => 0],
            $this->module->sGetPremiumShippingcosts($germany)
        );

        // With dispatch method
        $this->session->offsetSet('sDispatch', 9);
        $result = $this->module->sGetPremiumShippingcosts($germany);
        static::assertArrayHasKey('brutto', $result);
        static::assertArrayHasKey('netto', $result);
        static::assertArrayHasKey('value', $result);
        static::assertArrayHasKey('factor', $result);
        static::assertArrayHasKey('surcharge', $result);
        static::assertArrayHasKey('tax', $result);
    }

    /**
     * @param array $expected
     * @param array $actual
     */
    private function assertArray($expected, $actual)
    {
        foreach ($expected as $key => $value) {
            static::assertArrayHasKey($key, $actual);
            $currentActual = $actual[$key];

            if (is_array($value)) {
                $this->assertArray($value, $currentActual);
            } else {
                static::assertEquals($value, $currentActual);
            }
        }
    }

    /**
     * Create dummy customer entity
     *
     * @return \Shopware\Models\Customer\Customer
     */
    private function createDummyCustomer()
    {
        $date = new DateTime();
        $date->modify('-8 days');
        $lastLogin = $date->format(DateTime::ISO8601);

        $birthday = DateTime::createFromFormat('Y-m-d', '1986-12-20')->format(DateTime::ISO8601);

        $testData = [
            'password' => 'fooobar',
            'email' => uniqid(rand()) . 'test@foobar.com',
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

        $customerResource = new \Shopware\Components\Api\Resource\Customer();
        $customerResource->setManager(Shopware()->Models());

        return $customerResource->create($testData);
    }

    /**
     * Deletes all dummy customer entity
     */
    private function deleteDummyCustomer(\Shopware\Models\Customer\Customer $customer)
    {
        Shopware()->Db()->delete('s_user_addresses', 'user_id = ' . $customer->getId());
        Shopware()->Db()->delete('s_core_payment_data', 'user_id = ' . $customer->getId());
        Shopware()->Db()->delete('s_user_attributes', 'userID = ' . $customer->getId());
        Shopware()->Db()->delete('s_user', 'id = ' . $customer->getId());
    }
}
